# Architecture Analysis - TRC Project

## Current State
**Pattern**: Traditional MVC with Filament Admin Panel
**Stack**: Laravel 11 + Filament 3.2 + PHP 8.4

## Identified Issues

### 1. Anemic Domain Models
- Models only contain properties/relationships, no business logic
- Example: `User` model lacks domain behavior

### 2. Tight Coupling
- `InvoiceGenerator` coupled to TemplateProcessor, Notifications, Response
- Hard to test and reuse

### 3. Code Duplication
- `rasyidu()` and `edunesia()` methods 95% identical

### 4. God Objects
- `SalesForce` component: 500+ lines with multiple responsibilities
- Form schema, table columns, filters, query logic all mixed

### 5. Business Logic in Wrong Layer
- Calculations, formatting, file generation in controllers
- Should be in domain/service layer

### 6. Missing Abstractions
- No repository pattern
- No service layer
- No value objects
- No domain events

## Recommended Architecture

**Pattern**: Service Layer + Repository Pattern + DDD Tactical Patterns

### Why Not Full Clean/Hexagonal?
- Filament handles presentation
- Medium complexity project
- More practical for team size

## Proposed Directory Structure

```
app/
├── Domain/
│   ├── Sales/
│   │   ├── Entities/
│   │   │   ├── Registration.php
│   │   │   └── School.php
│   │   ├── ValueObjects/
│   │   │   ├── PhoneNumber.php
│   │   │   ├── SchoolAddress.php
│   │   │   ├── Period.php
│   │   │   └── ProgramType.php
│   │   ├── Services/
│   │   │   ├── RegistrationService.php
│   │   │   └── StatusTransitionService.php
│   │   ├── Repositories/
│   │   │   └── RegistrationRepositoryInterface.php
│   │   └── Events/
│   │       ├── RegistrationCreated.php
│   │       └── StatusChanged.php
│   ├── Finance/
│   │   ├── ValueObjects/
│   │   │   ├── Money.php
│   │   │   └── TaxRate.php
│   │   ├── Services/
│   │   │   ├── InvoiceGenerationService.php
│   │   │   └── TemplateStrategyInterface.php
│   │   └── Strategies/
│   │       ├── RasyiduTemplateStrategy.php
│   │       └── EdunesiaTemplateStrategy.php
│   └── Shared/
│       └── ValueObjects/
│           └── Email.php
│
├── Infrastructure/
│   ├── Persistence/
│   │   └── Eloquent/
│   │       └── RegistrationRepository.php
│   └── External/
│       └── PhpWordAdapter.php
│
└── Application/
    ├── DTOs/
    └── Commands/
```

## Bounded Contexts
1. **Sales Context** - Registration, Salesforce data
2. **Finance Context** - Invoices, payments
3. **Academic Context** - Academic data
4. **Administration Context** - Users, roles

## Implementation Priority

### Phase 1: Foundation (Quick Wins)
1. Create Domain directory structure
2. Extract InvoiceGenerator → InvoiceGenerationService + Strategy Pattern
3. Create PhoneNumber Value Object
4. Create Repository Interface for Registration

### Phase 2: Service Layer
1. Create RegistrationService
2. Move business logic from SalesForce component
3. Create StatusTransitionService
4. Implement Domain Events

### Phase 3: Rich Domain Models
1. Add behavior to Registration entity
2. Create School entity
3. Create Money, TaxRate value objects
4. Implement invariants in domain models

### Phase 4: Repository Implementation
1. Implement Eloquent repositories
2. Replace direct queries in Filament resources
3. Add caching layer

## Key Patterns to Apply

1. **Strategy Pattern** - Invoice templates
2. **Repository Pattern** - Data access abstraction
3. **Value Objects** - PhoneNumber, Money, Address
4. **Domain Events** - Decoupling side effects
5. **Service Layer** - Business logic orchestration
