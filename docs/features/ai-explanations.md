# AI-assisted explanations — deferred

No AI provider, generation endpoint, API key, or external content-sharing flow is
enabled in this implementation. The existing explanation field remains manually
editable. This document records the boundary rather than claiming a feature exists.

Provider setup is a separate decision. Before implementing it, select what content
may leave the local workspace, credentials, model, cost/rate limits, timeouts, and
retention policy. The eventual UI should preview generated text and require an
explicit save action. It must never replace an explanation or source automatically.

Future tests should cover authorization, provider failure, timeouts, usage limits,
safe rendering, explicit save/discard, and original-content preservation. No fake
generation button or fabricated explanation has been added.
