# Generated Excel Staging Files

These files were generated from:

- `C:\Users\User\Desktop\20251114 Senarai Tempahan Equipment Makmal to Dr Hamidon.xlsx`

Use them as the staging source before any real database import.

## Files

- `excel_catalog_summary.json`
  Summary counts for the extracted workbook.
- `excel_laboratories.csv`
  Unique laboratories with field, PIC, and service counts.
- `excel_lab_services.csv`
  Consolidated service rows, including merged continuation rows and grouped equipment models.
- `excel_row_staging.csv`
  Row-by-row normalized workbook output with source row numbers.
- `excel_validation_report.json`
  Missing criteria/model rows, unknown calibration rows, and continuation-row mapping.
- `pic_mapping_template.csv`
  Fill in the real PIC emails, phones, and usernames before any production import.

## Current Extract

- `13` laboratories
- `48` service offerings
- `56` normalized source rows
- `8` continuation rows merged into parent services
- `13` PIC names needing system account mapping

## Prototype Reset Command

After the staging files are generated, rebuild the prototype database with:

```bash
php spark slams:reset-prototype-catalog
```

That command:

- clears demo transaction data
- creates placeholder `pic01` to `pic13` accounts
- imports laboratories, services, and prototype assets
- writes `pic_placeholder_mapping.csv`

## Important Notes

- The workbook does not directly provide complete PIC account data.
- `pic_mapping_template.csv` must be completed before real import.
- The extractor treats blank service rows as continuation rows under the previous service in the same lab.
- `assets` should still be handled separately from this staging data, because many rows are services rather than maintainable equipment records.
