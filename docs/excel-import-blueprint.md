# Excel Import Blueprint

## Objective
Replace the current dummy laboratory master data with the real FKMP laboratory catalog from:

- `C:\Users\User\Desktop\20251114 Senarai Tempahan Equipment Makmal to Dr Hamidon.xlsx`

This blueprint is for the **data migration and model design** step before code changes.

## Current State

Current database snapshot:

- `3` laboratories
- `4` assets
- `29` bookings
- `35` maintenance records
- `12` active users

Current laboratories:

| ID | Laboratory | PIC Email |
| --- | --- | --- |
| 1 | Makmal Umum Bahasa Inggeris 1 | pic03@uthm.edu.my |
| 2 | Makmal Reka Bentuk Mekanikal | pic02@uthm.edu.my |
| 3 | Makmal Pneumatik & Hidraulik | pic01@uthm.edu.my |

Important observations:

- The current 3 laboratories are dummy/demo records and do **not** reflect the Excel source catalog.
- The current PIC accounts are placeholders (`pic01`, `pic02`, `pic03`) and do **not** match the real PIC names in the Excel workbook.
- The current `assets` table contains demo physical equipment only. It cannot represent the Excel workbook correctly on its own.

## Excel Source Snapshot

Parsed workbook summary:

- `54` usable rows
- `13` unique laboratories
- `13` unique PIC names
- `7` engineering fields
- `47` embedded media files
- `9` rows missing equipment model
- `11` rows missing acceptance criteria
- `12` rows with missing/placeholder calibration values

Engineering fields:

- ADVANCE MACHINING
- ENVIROMENTAL AND INDOOR AIR QUALITY
- MATERIAL PREPARATION
- MATERIALS CHARACTERIZATION
- MECHANICAL MATERIAL TESTING
- NDT
- VEHICLE AND ENGINE TESTING

Laboratories from Excel:

| Laboratory | Field | PIC | Service Rows |
| --- | --- | --- | ---: |
| Makmal Getaran & Kebisingan | NDT | Saiful Hazmi bin Sanip | 2 |
| Makmal Mekanik Mesin | NDT | Mohd Hamimi bin Masrom | 1 |
| Makmal Metalurgi | MATERIAL PREPARATION | Mohd Haidi bin Md Ishak | 3 |
| Makmal Pembakaran Dalam | VEHICLE AND ENGINE TESTING | Tc. MZahar bin Abd Jalal | 5 |
| Makmal Pemeriksaan & Pengujian | NDT | Tc. Muhamad Radhi bin Ujang | 3 |
| Makmal Pemesinan Jitu | ADVANCE MACHINING | Mohamad Faizal bin Jasman | 2 |
| Makmal Pemesinan Termaju | ADVANCE MACHINING | Mohd Raminhizad Bin Abd Razaman | 2 |
| Makmal Pencirian Bahan | MATERIALS CHARACTERIZATION | Nooriskandar bin Sani | 5 |
| Makmal Pengujian Mekanik | MECHANICAL MATERIAL TESTING | Mohd Yusof bin Mohd Sahil | 4 |
| Makmal Persekitaran Terma | ENVIROMENTAL AND INDOOR AIR QUALITY | Yaacub Zaki bin Ali | 8 |
| Makmal Polimer | MECHANICAL MATERIAL TESTING | Mohamad Shahrulelmi bin Mohd Rodzi | 7 |
| Makmal Sains Bahan | MATERIALS CHARACTERIZATION | Anuar bin Ismail | 5 |
| Makmal Seramik | MATERIALS CHARACTERIZATION | Fazlannuddin Hanur bin Harith | 7 |

## Key Interpretation

The Excel workbook is **not** a simple asset list.

It contains:

- laboratory master data
- service and testing offerings
- acceptance criteria for each service
- one or more equipment models used by a service
- calibration state
- PIC ownership
- photos

This means the system should not keep treating bookings as only:

- `book a lab`

The correct business flow is:

- choose a laboratory
- choose a service/test within that lab
- view acceptance criteria
- view calibration status
- route the request to the correct PIC

## Recommended Target Data Model

### 1. Laboratories
One row per unique laboratory from the Excel workbook.

Suggested columns:

- `id`
- `name`
- `room`
- `description`
- `field_name`
- `pic_name`
- `pic_email`
- `pic_phone`
- `pic_user_id` (recommended new foreign key)
- `image`

### 2. Lab Services
One row per **actual service/test offering**.

Suggested new table: `lab_services`

Suggested columns:

- `id`
- `laboratory_id`
- `field_name`
- `service_name`
- `acceptance_criteria`
- `calibration_status`
- `service_notes`
- `source_row_no`
- `is_active`

### 3. Service Equipment Models
The workbook shows that some services use multiple instruments/models.

Suggested new table: `service_equipment_models`

Suggested columns:

- `id`
- `service_id`
- `equipment_model`
- `source_row_no`
- `image`

This is needed because some Excel rows are clearly **continuations of the previous service**, not new services.

Examples:

- Row `39` in `Makmal Seramik` appears to be a second model under `Sintering Furnace`
- Rows `58-61` in `Makmal Persekitaran Terma` appear to be additional instruments under `Indoor Air Quality IAQ Assesment`
- Row `63` in `Makmal Persekitaran Terma` appears to be an additional instrument under `1.Thermal Comfort Measurement 2.Heat Stress Measurement 3.Hygiene Tech Measurement`

### 4. Assets
Keep `assets` only for true physical maintainable inventory.

Examples of likely real maintainable equipment:

- SEM/EDX machine
- XRD machine
- furnace
- hydraulic press
- potentiostat
- CNC lathe
- CNC milling machine

Do **not** force every service row into `assets`.

## Booking Implication

Yes, the real booking/request flow should include service selection.

Recommended future booking flow:

1. user selects laboratory
2. user selects service/test
3. system displays:
   - acceptance criteria
   - equipment model(s)
   - calibration status
   - PIC
4. user uploads request/supporting documents
5. booking/request is submitted against `lab_id + service_id`

Recommended booking table change:

- add `service_id` to `bookings`

## Required Data Cleanup Rules

### Calibration Normalization
Normalize all values to a controlled enum.

Suggested normalized values:

- `valid`
- `expired`
- `unknown`

Mapping:

- `YES`, `Yes`, `yes` -> `valid`
- `Expired`, `expired` -> `expired`
- `No`, `no`, `-`, blank -> `unknown`

### Blank Service Rows
Do not import blank service rows as standalone services.

Rules:

- if `service_name` is blank and `equipment_model` is present:
  attach the row as another `service_equipment_models` row under the previous non-blank service within the same lab

### Missing Criteria / Model
Do not reject the import for these rows.

Rules:

- allow nullable `acceptance_criteria`
- allow nullable `equipment_model`
- log missing values in the staging validation report

### Photos
The workbook has `47` embedded media files.

Rule:

- extract them during staging
- assign them later after row-to-image matching is verified

## PIC Mapping Requirement

The Excel workbook provides PIC names, but the system currently authorizes PICs by **email**.

This means a manual PIC mapping file is required before final import.

Required PIC mapping columns:

- `excel_pic_name`
- `system_full_name`
- `system_email`
- `system_phone`
- `username`
- `existing_user_id` or `new_user`

Current blocker:

- the current database only has placeholder PIC users:
  - `pic01@uthm.edu.my`
  - `pic02@uthm.edu.my`
  - `pic03@uthm.edu.my`
- these do not correspond to the real PIC names from the Excel workbook

Therefore:

- real PIC user accounts must be created or updated before production import

## Import Phases

### Phase 1. Backup and Freeze

- export current `laboratories`
- export current `assets`
- export current `bookings`
- export current `maintenance_records`
- confirm whether current bookings are disposable demo data

### Phase 2. Staging Import

Parse the Excel into staging data only.

Suggested staging outputs:

- `staging_laboratories`
- `staging_lab_services`
- `staging_service_equipment_models`
- validation report

### Phase 3. PIC Mapping

- prepare real PIC emails and phones
- create/update user accounts with `pic` role
- verify each Excel PIC has a corresponding system user

### Phase 4. Import Laboratories

- create the 13 real lab rows
- populate `pic_name`, `pic_email`, and eventually `pic_user_id`
- leave `room` nullable for now because the Excel workbook does not provide room values clearly

### Phase 5. Import Services

- create one `lab_services` row for each real service/test offering
- attach acceptance criteria
- attach normalized calibration status
- store source workbook row number

### Phase 6. Import Service Equipment Models

- create `service_equipment_models` rows for primary and continuation rows
- attach multiple models under a service where needed

### Phase 7. Rationalize Physical Assets

Decide which Excel entries should become `assets`.

Recommended rule:

- import to `assets` only if the item is a physical unit that should support:
  - maintenance tracking
  - quantity
  - status
  - fault reporting
  - predictive maintenance

### Phase 8. Update Booking Logic

- add `service_id` to booking creation
- update public lab booking UI to require service selection
- show criteria and calibration warnings before submit

### Phase 9. Retire Dummy Data

After staging validation and final import:

- archive or remove dummy laboratories
- archive or remove dummy assets
- decide whether existing demo bookings should be cleared

## Decisions Needed Before Coding

1. Are the current `29` bookings and `35` maintenance records disposable demo data?
2. Do you want service bookings to be required immediately, or staged in after the import?
3. Do you have a separate source for:
   - PIC emails
   - PIC phone numbers
   - room numbers
4. Should the `assets` table be repopulated only with maintainable equipment, or should service equipment models remain separate from assets?
5. Should continuation rows with blank service names always be attached to the previous service in the same lab?  
   Recommendation: `yes`

## Recommended Immediate Deliverables

Before touching production code, produce these artifacts:

1. PIC mapping sheet
2. normalized staging export from Excel
3. validation report listing:
   - missing service names
   - missing criteria
   - missing equipment models
   - inconsistent calibration values
4. final import map for:
   - laboratories
   - lab services
   - service equipment models
   - physical assets

## Generated Staging Outputs

The first staging pass has now been generated under:

- [docs/generated/README.md](/C:/laragon/www/slams/docs/generated/README.md)
- [docs/generated/excel_catalog_summary.json](/C:/laragon/www/slams/docs/generated/excel_catalog_summary.json)
- [docs/generated/excel_laboratories.csv](/C:/laragon/www/slams/docs/generated/excel_laboratories.csv)
- [docs/generated/excel_lab_services.csv](/C:/laragon/www/slams/docs/generated/excel_lab_services.csv)
- [docs/generated/excel_row_staging.csv](/C:/laragon/www/slams/docs/generated/excel_row_staging.csv)
- [docs/generated/excel_validation_report.json](/C:/laragon/www/slams/docs/generated/excel_validation_report.json)
- [docs/generated/pic_mapping_template.csv](/C:/laragon/www/slams/docs/generated/pic_mapping_template.csv)

Current extracted counts:

- `13` laboratories
- `48` service offerings
- `56` normalized source rows
- `8` continuation rows
- `13` PIC names requiring system account mapping

These staging files should be treated as the working import source for the next database migration step.

Prototype reset/import command:

```bash
php spark slams:reset-prototype-catalog
```

This command clears demo transaction data, creates placeholder PIC accounts, and rebuilds the prototype catalog from the staged Excel extract.

## Scripts Added For Audit

Supporting audit scripts created during this planning step:

- [scripts/import_blueprint_audit.php](/C:/laragon/www/slams/scripts/import_blueprint_audit.php)
- [scripts/extract_excel_catalog.py](/C:/laragon/www/slams/scripts/extract_excel_catalog.py)
- [scripts/maintenance_ml_inspect.php](/C:/laragon/www/slams/scripts/maintenance_ml_inspect.php)
- [app/Commands/ResetPrototypeCatalog.php](/C:/laragon/www/slams/app/Commands/ResetPrototypeCatalog.php)

These are for local inspection and planning, not the final import itself.
