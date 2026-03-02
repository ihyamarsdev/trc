# Complete Resource Analysis - TRC Project
## Date: 2025-02-20

## All Resources Analysis

### User Panel Resources (`app/Filament/User/Resources/`)

#### 1. Salesforce Resource (`Salesforce/SalesResource.php`)
- **Model**: RegistrationData
- **Component**: `SalesForce` - 500+ lines (GOD OBJECT)
- **Navigation Group**: Salesforce
- **Role**: sales
- **Methods Delegated**:
  - `schema()` → SalesForce::schema()
  - `columns()` → SalesForce::columns()
  - `filters()` → SalesForce::filters()
  - `infolist()` → SalesForce::infolist()
- **Query Filter**: Current year + user's own records + order by implementation_estimate

#### 2. Finance Resource (`Finance/FinanceResource.php`)
- **Model**: RegistrationData
- **Component**: `Finance` - 400+ lines (GOD OBJECT)
- **Navigation Group**: Finance
- **Role**: finance
- **Methods Delegated**:
  - `formSchema()` → Finance::formSchema()
  - `columns()` → Finance::columns()
  - `filters()` → Finance::filters()
  - `infolist()` → Finance::infolist()
- **Query Filter**: Current year + status order >= 7 (paid stages) + order by updated_at
- **Special**: Has `exportColumns()` method for export

#### 3. Academic Resource (`Academic/AcademicResource.php`)
- **Model**: RegistrationData
- **Component**: `Academic` - 300+ lines (GOD OBJECT)
- **Navigation Group**: Service
- **Role**: service
- **Methods Delegated**:
  - `formSchema()` → Academic::formSchema()
  - `columns()` → Academic::columns()
  - `filters()` → Academic::filters()
- **Query Filter**: Current year + status order between 2-10 (service stages)
- **Special**: Has `TextColumns()` method and `getDifference()` calculation

#### 4. Activity Resource (`Activity/ActivityResource.php`)
- **Model**: RegistrationData
- **Component**: `Admin` (reused!) - INCONSISTENT
- **Navigation Label**: Activity
- **No Navigation Group**: Standalone
- **Query Filter**: All records, filtered by user role if sales
- **Special**: Uses `recordUrl` to redirect to activities page

#### 5. Admin Resource (`Admin/AdminResource.php`)
- **Model**: RegistrationData
- **Component**: `Admin` - 500+ lines (GOD OBJECT)
- **Navigation Label**: Admin Database
- **Role**: admin only
- **Query Filter**: All records (no filtering)

### Admin Panel Resources (`app/Filament/Resources/`)

#### 6. User Resource
- **Model**: User
- **Navigation**: Admin panel
- **Role**: admin

#### 7. Role Resource
- **Model**: Role (Spatie Permission)
- **Navigation**: Admin panel
- **Role**: admin

#### 8. Timeline Resource (`app/Filament/User/Resources/TimelineResource.php`)
- **Model**: RegistrationData
- **Navigation Group**: None (standalone)

## Common Patterns Found

### 1. Meta Function Duplication
ALL components (SalesForce, Finance, Academic) have this DUPLICATE code:

```php
protected static function meta(Get $get): array {
    $type = $get("type");
    return match ($type) {
        "anbk" => ["nameRegister" => "ANBK", ...],
        "apps" => ["nameRegister" => "APPS", ...],
        "snbt" => ["nameRegister" => "SNBT", ...],
        "tka" => ["nameRegister" => "TKA", ...],
        default => [...]
    };
}
```

**Found in**: SalesForce, Finance, Academic (3x duplication!)

### 2. Status Color Badge Duplication
ALL components have this DUPLICATE code:

```php
TextColumn::make("latestStatusLog.status.color")
    ->badge()
    ->formatStateUsing(fn($state) => ucfirst($state))
    ->color(fn(string $state): string => match ($state) {
        "green" => "green",
        "blue" => "blue",
        "yellow" => "yellow",
        "red" => "red",
    })
```

**Found in**: SalesForce, Finance, Academic, Activity (4x duplication!)

### 3. Icon Entry Logic Duplication
ALL components have this DUPLICATE complex icon/color logic:

```php
IconEntry::make("latestStatusLog.status.order")
    ->icon(function ($state) {
        static $iconByOrder;
        if ($iconByOrder === null) {
            $iconByOrder = Status::query()->pluck("icon", "order")->all();
        }
        return $iconByOrder[(int)$state] ?? "heroicon-m-clock";
    })
    ->color(function ($state) {
        static $colorByOrder;
        if ($colorByOrder === null) {
            $colorByOrder = Status::query()->pluck("color", "order")->all();
        }
        // ... match logic
    })
```

**Found in**: SalesForce, Finance, Academic (3x duplication!)

### 4. Infolist Salesforce Section Duplication
ALL components have NEAR-IDENTICAL Salesforce section in infolist:

```php
Infolists\Components\Section::make("Salesforce")
    ->schema([
        Fieldset::make("Periode")->schema([...]),
        Fieldset::make("Salesforce")->schema([...]),
        Fieldset::make("Sekolah")->schema([...]),
        Fieldset::make("Bagan")->schema([...]),
        Fieldset::make("")->schema([...]),
    ])
```

**Found in**: SalesForce, Finance, Academic (3x duplication!)

## Critical Findings

### Code Duplication Summary
- **Meta function**: 3x duplications (80 lines each = 240 lines wasted)
- **Status badge**: 4x duplications (15 lines each = 60 lines wasted)
- **Icon entry logic**: 3x duplications (30 lines each = 90 lines wasted)
- **Salesforce infolist**: 3x duplications (100 lines each = 300 lines wasted)

**Total**: ~690 lines of duplicate code across components!

### God Objects Summary
1. **SalesForce**: 500+ lines (schema, columns, filters, infolist)
2. **Finance**: 400+ lines (formSchema, columns, filters, infolist, exportColumns)
3. **Academic**: 300+ lines (formSchema, columns, filters, TextColumns, infolist, getDifference)
4. **Admin**: 500+ lines (estimated)

### Component Inconsistencies
- **ActivityResource** uses `Admin` component instead of its own - CONFUSING!
- **Finance** has `exportColumns()` but others don't
- **Academic** has `TextColumns()` but others don't
- Some use `formSchema()`, some use `schema()`

### Missing Abstractions
1. No base component class
2. No shared trait for common functionality
3. No dedicated Status badge component
4. No reusable Salesforce infolist section

## Recommended Immediate Actions

### 1. Create Base Component Class
```php
abstract class BaseRegistrationComponent {
    protected static function getProgramMeta(string $type): array {
        return match($type) { ... };
    }
    
    protected static function getStatusBadgeColumn(): TextColumn {
        // Common status badge logic
    }
    
    protected static function getSalesforceInfolistSection(): InfolistComponent {
        // Common Salesforce section
    }
}
```

### 2. Create Shared Traits
```php
trait HasProgramMeta {
    protected static function meta(Get $get): array {
        return self::getProgramMeta($get('type'));
    }
}

trait HasStatusDisplay {
    public static function statusBadgeColumn(): TextColumn { ... }
    public static function statusIconEntry(): IconEntry { ... }
}
```

### 3. Extract to Dedicated Classes
```
app/Filament/Components/Shared/
├── ProgramMeta.php (enum with label/description)
├── StatusBadgeColumn.php
├── SalesforceInfolistSection.php
└── IconEntryHelper.php
```

### 4. Standardize Component Structure
All components should have:
- `formSchema()` (consistent naming)
- `tableColumns()` (not just `columns()`)
- `tableFilters()` (not just `filters()`)
- `detailInfolist()` (not just `infolist()`)

## Estimated Impact

| Metric | Current | After Improvement |
|--------|---------|-------------------|
| Duplicate code | ~690 lines | 0 lines |
| Component sizes | 300-500 lines | 100-200 lines |
| Maintainability | Low | High |
| Consistency | Low | High |
