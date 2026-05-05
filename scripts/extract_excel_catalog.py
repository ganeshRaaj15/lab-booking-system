from __future__ import annotations

import csv
import json
import re
import sys
import zipfile
from pathlib import Path
from typing import Dict, List
from xml.etree import ElementTree as ET


DEFAULT_WORKBOOK = Path(r"C:\Users\User\Desktop\20251114 Senarai Tempahan Equipment Makmal to Dr Hamidon.xlsx")
MAIN_NS = {"a": "http://schemas.openxmlformats.org/spreadsheetml/2006/main"}
OUTPUT_FILENAMES = {
    "summary": "excel_catalog_summary.json",
    "labs": "excel_laboratories.csv",
    "services": "excel_lab_services.csv",
    "rows": "excel_row_staging.csv",
    "validation": "excel_validation_report.json",
    "pics": "pic_mapping_template.csv",
}


def main() -> int:
    workbook_path = Path(sys.argv[1]) if len(sys.argv) > 1 else DEFAULT_WORKBOOK
    if not workbook_path.is_file():
        print(f"Workbook not found: {workbook_path}", file=sys.stderr)
        return 1

    project_root = Path(__file__).resolve().parents[1]
    output_dir = project_root / "docs" / "generated"
    output_dir.mkdir(parents=True, exist_ok=True)

    rows = load_sheet_rows(workbook_path)
    header_row = detect_header_row(rows)
    if header_row is None:
        print("Unable to detect workbook header row.", file=sys.stderr)
        return 1

    normalized_rows: List[dict] = []
    services: Dict[str, dict] = {}
    labs: Dict[str, dict] = {}
    pic_names: Dict[str, bool] = {}
    validation = {
        "header_row": header_row,
        "raw_calibration_values": set(),
        "missing_acceptance_criteria_rows": [],
        "missing_equipment_model_rows": [],
        "continuation_rows": [],
        "orphan_continuation_rows": [],
        "unknown_calibration_rows": [],
    }

    current_field = ""
    current_lab = ""
    current_pic = ""
    current_service_key: str | None = None

    for row_number in sorted(rows.keys()):
        if row_number <= header_row:
            continue

        row = rows[row_number]
        sequence_no = clean_cell(row.get("B"))
        field_name = clean_cell(row.get("C"))
        laboratory_name = clean_cell(row.get("D"))
        service_name = clean_cell(row.get("E"), preserve_breaks=True)
        acceptance_criteria = clean_cell(row.get("F"), preserve_breaks=True)
        equipment_model = clean_cell(row.get("G"), preserve_breaks=True)
        calibration_raw = clean_cell(row.get("H"))
        pic_name = clean_cell(row.get("J"))

        field_name = field_name or current_field
        laboratory_name = laboratory_name or current_lab
        pic_name = pic_name or current_pic

        current_field = field_name or current_field
        current_lab = laboratory_name or current_lab
        current_pic = pic_name or current_pic

        if not any(
            [
                sequence_no,
                field_name,
                laboratory_name,
                service_name,
                acceptance_criteria,
                equipment_model,
                calibration_raw,
                pic_name,
            ]
        ):
            continue

        row_type = determine_row_type(service_name, acceptance_criteria, equipment_model, calibration_raw)
        if row_type == "blank":
            continue

        calibration_status = normalize_calibration(calibration_raw)
        validation["raw_calibration_values"].add(calibration_raw.upper())

        normalized_row = {
            "source_row_no": row_number,
            "sequence_no": sequence_no,
            "field_name": field_name,
            "laboratory_name": laboratory_name,
            "service_name": service_name,
            "row_type": row_type,
            "acceptance_criteria": acceptance_criteria,
            "equipment_model": equipment_model,
            "calibration_raw": calibration_raw,
            "calibration_status": calibration_status,
            "pic_name": pic_name,
        }
        normalized_rows.append(normalized_row)

        if acceptance_criteria == "":
            validation["missing_acceptance_criteria_rows"].append(compact_row_reference(normalized_row))
        if equipment_model == "":
            validation["missing_equipment_model_rows"].append(compact_row_reference(normalized_row))
        if calibration_status == "unknown":
            validation["unknown_calibration_rows"].append(compact_row_reference(normalized_row))

        lab_entry = labs.setdefault(
            laboratory_name,
            {
                "field_name": field_name,
                "laboratory_name": laboratory_name,
                "pic_name": pic_name,
                "source_row_count": 0,
                "service_count": 0,
                "continuation_count": 0,
            },
        )
        lab_entry["source_row_count"] += 1

        if pic_name:
            pic_names[pic_name] = True

        if row_type == "service":
            current_service_key = build_service_key(laboratory_name, service_name)
            if current_service_key not in services:
                services[current_service_key] = {
                    "service_key": current_service_key,
                    "field_name": field_name,
                    "laboratory_name": laboratory_name,
                    "service_name": service_name,
                    "acceptance_criteria_values": set(),
                    "equipment_models": set(),
                    "calibration_statuses": set(),
                    "source_rows": [],
                    "pic_name": pic_name,
                    "continuation_count": 0,
                }
                lab_entry["service_count"] += 1
        else:
            if current_service_key is None or current_service_key not in services:
                validation["orphan_continuation_rows"].append(compact_row_reference(normalized_row))
                continue
            validation["continuation_rows"].append(
                {
                    "source_row_no": row_number,
                    "laboratory_name": laboratory_name,
                    "parent_service": services[current_service_key]["service_name"],
                }
            )
            services[current_service_key]["continuation_count"] += 1
            lab_entry["continuation_count"] += 1

        if acceptance_criteria:
            services[current_service_key]["acceptance_criteria_values"].add(acceptance_criteria)
        if equipment_model:
            services[current_service_key]["equipment_models"].add(equipment_model)
        services[current_service_key]["calibration_statuses"].add(calibration_status)
        services[current_service_key]["source_rows"].append(row_number)

    validation["raw_calibration_values"] = sorted(validation["raw_calibration_values"])

    laboratory_rows = [labs[name] for name in sorted(labs.keys())]
    service_rows = []
    for key in sorted(services.keys()):
        service = services[key]
        service_rows.append(
            {
                "service_key": service["service_key"],
                "field_name": service["field_name"],
                "laboratory_name": service["laboratory_name"],
                "service_name": service["service_name"],
                "acceptance_criteria": " || ".join(sorted(service["acceptance_criteria_values"])),
                "equipment_models": " | ".join(sorted(service["equipment_models"])),
                "calibration_status": merge_calibration_status(list(service["calibration_statuses"])),
                "source_rows": ",".join(str(item) for item in service["source_rows"]),
                "pic_name": service["pic_name"],
                "continuation_count": service["continuation_count"],
            }
        )

    summary = {
        "workbook_path": str(workbook_path),
        "output_directory": str(output_dir),
        "counts": {
            "normalized_rows": len(normalized_rows),
            "laboratories": len(laboratory_rows),
            "services": len(service_rows),
            "pics": len(pic_names),
            "continuation_rows": len(validation["continuation_rows"]),
            "rows_missing_acceptance_criteria": len(validation["missing_acceptance_criteria_rows"]),
            "rows_missing_equipment_model": len(validation["missing_equipment_model_rows"]),
            "rows_with_unknown_calibration": len(validation["unknown_calibration_rows"]),
            "orphan_continuation_rows": len(validation["orphan_continuation_rows"]),
        },
    }

    write_csv(
        output_dir / OUTPUT_FILENAMES["rows"],
        [
            "source_row_no",
            "sequence_no",
            "field_name",
            "laboratory_name",
            "service_name",
            "row_type",
            "acceptance_criteria",
            "equipment_model",
            "calibration_raw",
            "calibration_status",
            "pic_name",
        ],
        normalized_rows,
    )
    write_csv(
        output_dir / OUTPUT_FILENAMES["labs"],
        [
            "field_name",
            "laboratory_name",
            "pic_name",
            "source_row_count",
            "service_count",
            "continuation_count",
        ],
        laboratory_rows,
    )
    write_csv(
        output_dir / OUTPUT_FILENAMES["services"],
        [
            "service_key",
            "field_name",
            "laboratory_name",
            "service_name",
            "acceptance_criteria",
            "equipment_models",
            "calibration_status",
            "source_rows",
            "pic_name",
            "continuation_count",
        ],
        service_rows,
    )
    write_csv(
        output_dir / OUTPUT_FILENAMES["pics"],
        [
            "excel_pic_name",
            "system_full_name",
            "system_email",
            "system_phone",
            "username",
            "existing_user_id",
            "new_user",
        ],
        [
            {
                "excel_pic_name": name,
                "system_full_name": "",
                "system_email": "",
                "system_phone": "",
                "username": "",
                "existing_user_id": "",
                "new_user": "yes",
            }
            for name in sorted(pic_names.keys())
        ],
    )

    (output_dir / OUTPUT_FILENAMES["validation"]).write_text(json.dumps(validation, indent=2), encoding="utf-8")
    (output_dir / OUTPUT_FILENAMES["summary"]).write_text(json.dumps(summary, indent=2), encoding="utf-8")
    print(json.dumps(summary, indent=2))
    return 0


def load_sheet_rows(workbook_path: Path) -> Dict[int, Dict[str, str]]:
    rows: Dict[int, Dict[str, str]] = {}
    with zipfile.ZipFile(workbook_path) as workbook:
        shared_strings = load_shared_strings(workbook)
        sheet_xml = workbook.read("xl/worksheets/sheet1.xml")

    sheet_root = ET.fromstring(sheet_xml)
    for row in sheet_root.find("a:sheetData", MAIN_NS).findall("a:row", MAIN_NS):
        row_number = int(row.attrib.get("r", "0"))
        values: Dict[str, str] = {}
        for cell in row.findall("a:c", MAIN_NS):
            reference = cell.attrib.get("r", "")
            column = re.match(r"[A-Z]+", reference).group(0)
            cell_type = cell.attrib.get("t", "")
            values[column] = read_cell_value(cell, cell_type, shared_strings)
        rows[row_number] = values
    return rows


def load_shared_strings(workbook: zipfile.ZipFile) -> List[str]:
    if "xl/sharedStrings.xml" not in workbook.namelist():
        return []

    root = ET.fromstring(workbook.read("xl/sharedStrings.xml"))
    values = []
    for item in root.findall("a:si", MAIN_NS):
        text = "".join(node.text or "" for node in item.iterfind(".//a:t", MAIN_NS))
        values.append(text)
    return values


def read_cell_value(cell: ET.Element, cell_type: str, shared_strings: List[str]) -> str:
    value = cell.find("a:v", MAIN_NS)
    if cell_type == "s" and value is not None and value.text is not None:
        return shared_strings[int(value.text)]
    if cell_type == "inlineStr":
        return "".join(node.text or "" for node in cell.iterfind(".//a:t", MAIN_NS))
    return value.text if value is not None and value.text is not None else ""


def detect_header_row(rows: Dict[int, Dict[str, str]]) -> int | None:
    for row_number, row in rows.items():
        if (
            (row.get("E", "").strip().upper() == "SERVICES & TESTING")
            and (row.get("D", "").strip().upper() == "LABORATORY")
            and (row.get("J", "").strip().upper() == "PIC")
        ):
            return row_number
    return None


def clean_cell(value: str | None, preserve_breaks: bool = False) -> str:
    if value is None:
        return ""
    text = value.replace("\r", "").strip()
    if not text:
        return ""
    if preserve_breaks:
        lines = [line.strip() for line in re.split(r"\n+", text) if line.strip()]
        return " / ".join(lines)
    return re.sub(r"\s+", " ", text)


def determine_row_type(service_name: str, acceptance_criteria: str, equipment_model: str, calibration_raw: str) -> str:
    if service_name:
        return "service"
    if acceptance_criteria or equipment_model or calibration_raw:
        return "continuation"
    return "blank"


def normalize_calibration(calibration_raw: str) -> str:
    normalized = calibration_raw.strip().upper()
    if normalized == "YES":
        return "valid"
    if normalized == "EXPIRED":
        return "expired"
    if normalized in {"", "-", "NO"}:
        return "unknown"
    return "unknown"


def merge_calibration_status(statuses: List[str]) -> str:
    unique = set(statuses)
    if "expired" in unique:
        return "expired"
    if "valid" in unique:
        return "valid"
    return "unknown"


def build_service_key(laboratory_name: str, service_name: str) -> str:
    return f"{laboratory_name} :: {service_name}"


def compact_row_reference(row: dict) -> dict:
    return {
        "source_row_no": row["source_row_no"],
        "laboratory_name": row["laboratory_name"],
        "service_name": row["service_name"],
        "equipment_model": row["equipment_model"],
        "calibration_raw": row["calibration_raw"],
    }


def write_csv(path: Path, headers: List[str], rows: List[dict]) -> None:
    with path.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=headers)
        writer.writeheader()
        writer.writerows(rows)


if __name__ == "__main__":
    raise SystemExit(main())
