# Audit Log PRD & Architecture Specification

## 1. Overview
The **Audit Log System** provides a complete, immutable, and searchable activity log across the ICHA Conference application. It tracks critical admin and reviewer actions to ensure transparency, accountability, and seamless dispute resolution for registrations, payments, and abstract reviews.

---

## 2. Event Scope (Which Actions Are Audited)

### A. Payment & Registration Events
- `PAYMENT_SUBMITTED`: Participant uploads proof of payment.
- `PAYMENT_VERIFIED`: Admin approves a payment.
- `PAYMENT_REJECTED`: Admin rejects a payment with a reason.

### B. Abstract & Review Events
- `ABSTRACT_SUBMITTED`: Participant submits a new abstract.
- `REVIEWER_ASSIGNED`: Admin assigns 1-3 reviewers to an abstract round.
- `REVIEW_SUBMITTED`: Reviewer submits scores (Criteria 1, Criteria 2, recommendation, notes).
- `REVIEW_ROUND_LOCKED`: System automatically locks a round when 3/3 reviews are completed.
- `ABSTRACT_DECISION_UPDATED`: Scientific Committee / Admin sets final abstract status (`accepted`, `rejected`, `revision_required`).

### C. Master Data & System Events
- `REVIEWER_ACCOUNT_CREATED`: Admin creates a reviewer account and assigns track specializations.
- `REVIEWER_ACCOUNT_UPDATED`: Admin updates reviewer details or track specializations.
- `TIMELINE_UPDATED`: Admin alters conference timelines or deadlines.

---

## 3. Database Schema Specification (`audit_logs`)

```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL, -- Actor who performed the action (NULL if system auto-action)
    action VARCHAR(255) NOT NULL, -- Event key e.g. PAYMENT_VERIFIED
    auditable_type VARCHAR(255) NULL, -- Morph model type e.g. App\Models\Payment
    auditable_id BIGINT UNSIGNED NULL, -- Morph model ID
    old_values JSON NULL, -- State before the action
    new_values JSON NULL, -- State after the action
    ip_address VARCHAR(45) NULL, -- Client IPv4 / IPv6
    user_agent TEXT NULL, -- Browser / Device signature
    created_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 4. Architectural Design (Service Pattern)

### `AuditLogService` Class Definition (`App\Services\AuditLogService`)

```php
namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public static function record(
        string $action,
        mixed $model = null,
        array $oldValues = [],
        array $newValues = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model ? $model->id : null,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
```

---

## 5. UI Specification (Admin Audit Trail View)

- **Route**: `GET /admin/audit-logs`
- **Controller**: `App\Http\Controllers\Admin\AuditLogController`
- **Inertia Page**: `resources/js/Pages/Admin/AuditLogs/Index.vue`
- **Features**:
  - Filter by Action (`All`, `Payments`, `Submissions`, `Reviewers`).
  - Search by Actor Name / Email / Code.
  - Expandable JSON diff modal showing Before & After values.
  - Human-readable timestamps (e.g., `12 Aug 2026, 21:45:00 WIB`).
