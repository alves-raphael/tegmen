# Policies Lifecycle

- There 4 states for a policy: `ACTIVE`, `RENEWED`, `EXPIRED` and `CANCELLED`.
- When a new policy is created, it starts in the `ACTIVE` state.
- An `ACTIVE` policy can transition to `RENEWED` when the user renews.
- A policy will transition to `EXPIRED` when the end date is reached and the policy is not renewed or cancelled.
- A policy can be `CANCELLED` by the user or the insurance company at any time before it expires. Once cancelled, the policy cannot be reactivated or renewed.