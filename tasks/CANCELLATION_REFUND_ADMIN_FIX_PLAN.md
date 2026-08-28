# Paid Cancellation Refund — Admin Processing Fix Plan

**Status:** IMPLEMENTED — focused and full regression qualification pending
**Date:** 29 August 2026
**Accountable:** Agent 0

## Diagnosis

Razorpay does not automatically refund merely because the application order is cancelled. For a paid customer cancellation, Rythme correctly creates one full-value local refund reservation in `pending` state and marks the order payment `refund_pending`. This prevents losing the refund obligation and reserves the captured amount.

The Filament `Refund` action incorrectly always creates another refund reservation before processing. Because the cancellation reservation already holds the full captured amount, the second reservation is correctly rejected with `Refund total cannot exceed the captured payment amount.`

## Simple fix

1. Keep cancellation behavior unchanged: create one durable pending refund; do not call Razorpay from the customer request.
2. Add a distinct finance-only `Process pending refund` admin action.
3. Process the existing pending reservation through the approved gateway instead of creating a duplicate.
4. Hide the manual `Refund` request action while the payment has any pending/processing refund, preventing duplicate reservation and unknown-outcome retries.
5. Preserve current partial-refund behavior when no pending/processing operation exists.
6. Keep provider-pending outcomes in `processing`; never blindly retry them.

## Regression gates

- Paid cancellation creates exactly one pending full refund and no gateway call.
- Finance processing uses that same refund ID and makes one gateway call.
- Manual refund action cannot create a second reservation while pending/processing exists.
- Existing partial/full refund, idempotency, authorization, reconciliation, notification and order-cancellation tests remain green.
- No migration or schema change is required.
