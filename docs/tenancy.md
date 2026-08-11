# Tenancy and authorization

## Ownership model

The application uses one MySQL database and a visible `organization_id` foreign key on every tenant-owned record. Each user belongs to one organization, and each ticket stores both its organization and creator.

There is no global Eloquent scope. Tenant boundaries remain visible in controllers, middleware, policies, validation, and tests.

## Request boundaries

Ticket list queries always begin with the authenticated user's organization. Search conditions are nested in a grouped clause so `OR` conditions cannot escape the tenant restriction.

Detail and status-update routes pass through `ResolveTenantTicket` after authentication. The middleware finds the requested ticket within the authenticated organization and returns `404` when it is absent, including when the identifier belongs to another tenant.

`TicketPolicy` provides a separate authorization layer:

- Authenticated members and managers can list, view, and create tickets for their organization.
- Only managers can update ticket status.
- Every record-based policy method compares organization IDs.

Dedicated Form Requests validate ticket creation and status updates. The create controller derives `organization_id`, `created_by`, and initial `open` status from the authenticated user and never mass-assigns ownership fields.

## Verification

The MySQL-backed feature suite covers tenant-scoped lists, cross-tenant `404` responses, server-derived ownership, manager/member permissions, grouped search, combined filtering, and pagination query preservation. A bootstrap-level database override prevents the suite from targeting the development database.
