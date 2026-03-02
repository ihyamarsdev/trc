# Project Analysis - TRC (The Rasyidu Center)
## Date: 2025-02-20

## Project Overview
- Dashboard administrasi sekolah
- Laravel 11 + Filament 3.2 + PHP 8.4
- SQLite database
- Two Filament panels: Admin (/admin) and User (/)

## Current Architecture Issues

### 1. God Table - registration_data (50+ columns)
- Sales columns: type, periode, years, provinces, regencies, district, area, etc.
- Service columns: group, bimtek, account_count_created, etc.
- Finance columns: price, total, net, invoice_date, payment_date, etc.
- Invoice columns: detail_invoice, number_invoice, qty_invoice, unit_price, etc.
- Receipt columns: detail_kwitansi, difference_total, etc.

### 2. Code Duplication - Controllers (CRITICAL)
Pattern duplication in ANBK, Kwitansi, InvoiceGenerator controllers:
- Each has rasyidu() and edunesia() methods that are 95-99% identical
- Only differences: template path and document name

Example ANBK.php:
- rasyidu(): template='template/rasyidu/spk.docx', name='SPK RASYIDUU ANBK'
- edunesia(): template='template/edunesia/spk.docx', name='SPK EDUNESIA APPS'

### 3. God Object - SalesForce Component (500+ lines)
- schema(), columns(), filters(), infolist() all in one class
- Mixed responsibilities: form schema, table columns, filters, query logic

### 4. Anemic Domain Models
- RegistrationData: Only fillable fields and casts
- User: Mostly trait-based, minimal business logic
- No domain behavior in models

### 5. Missing Service Layer
- Business logic in Controllers (InvoiceGenerator, ANBK, Kwitansi)
- Document generation logic tightly coupled to controllers
- No abstraction for template processing

### 6. No Testing
- Only ExampleTest.php in Feature and Unit
- No tests for business logic
- No tests for document generation

### 7. Route Duplication
Routes for each document type (anbk, apps, snbt, kwitansi, invoice)
Each with rasyidu and edunesia variants = 10+ routes for similar functionality

## Current Structure
```
app/
├── Filament/
│   ├── User/Resources/ (Salesforce, Finance, Academic, Activity, Admin)
│   ├── Components/ (SalesForce, Academic, Admin, Finance, Format)
│   └── Enum/ (Jenjang, Periode, Program)
├── Http/Controllers/ (InvoiceGenerator, ANBK, Kwitansi, SNBT, TKA, APPS)
├── Models/ (User, RegistrationData, RegistrationStatus, Status)
└── Livewire/ (DetailProfile, EditProfile)
```

## Recommended Improvements (Priority Order)

### Priority 1: Quick Wins (Immediate Impact)
1. Document Generation Service with Strategy Pattern
2. Extract Value Objects (PhoneNumber, Money)
3. Split SalesForce Component into focused classes

### Priority 2: Service Layer
1. Create Domain/Services directory
2. Move business logic from controllers to services
3. Implement Repository Interfaces

### Priority 3: Testing
1. Create Feature tests for document generation
2. Create Unit tests for business logic
3. Test Filament resources

### Priority 4: Data Structure
1. Split registration_data into bounded contexts
2. Create proper entities and aggregates
3. Implement Domain Events

## Dependencies Used
- Filament v3.2 (Admin panel)
- Livewire v3.5
- Spatie Laravel Permission v6.9
- Laravel Daily Invoices v4.0
- PhpOffice\PhpWord v1.3
- Barryvdh DomPDF/Snappy (PDF generation)
- Maatwebsite Excel v3.1
- Creasi Laravel Nusa v0.1.8 (Indonesia regions)
