# Faculty Data Migration & Static Cleanups

This file documents the cleanups performed to enforce dynamic data flow for faculties, ensuring no hardcoded references exist in frontend sources or seeders.

## 1. Faculty of Technology

- **Status**: Dynamic migration complete.
- **Summary**: All 6 departments (Oil/Gas Refining, Food Tech, Chemical Eng, Agricultural Storage, Oil/Gas Engineering, Metrology) and their associated bachelor/master programs are loaded dynamically from MySQL tables.
- **Dean's Office**: Dean Dr. Rashid Tokhtayevich Adizov, details managed dynamically.

## 2. Faculty of Engineering

- **Status**: Cleaned and Dynamic.
- **Summary**: All references to Faculty of Engineering, its 6 departments, and 18 programs have been purged from static code in `apps/web/src` and main seeders to prevent static display.
- **Restoration**: Restored dynamically in the active database via custom SQL migrations.
- **Department List**:
  1. Department of Electrical and Power Engineering (`electrical-power-engineering`)
  2. Department of Architecture (`architecture`)
  3. Department of Civil Engineering (`civil-engineering`)
  4. Department of Light Industry Engineering and Design (`light-industry-engineering-and-design`)
  5. Department of Mechanics and Engineering Graphics (`mechanics-engineering-graphics`)
  6. Department of Technological Machines and Equipment (`technological-machines-equipment`)
