---
name: knotbane
description: Use Knotbane to measure and improve cyclomatic complexity or tangled control flow in PHP code.
---

# Knotbane

Use Knotbane as a review signal. The goal is code that is easier to understand and change, not the lowest score.

## Workflow

1. Before editing, choose the smallest analysis scope that covers every PHP code unit whose branching may change, including units that may receive decisions moved from elsewhere. Exclude unrelated hotspots unless the user requests broader work.

2. Run the project-installed `vendor/bin/knotbane` when available. Otherwise, run Knotbane through CPX with `cpx adiachenko/knotbane`. Use CPX's default cache unless the execution environment restricts home-directory writes. If it does, set `CPX_HOME` to a writable temporary directory supplied by the environment before the first invocation. Handle CPX cache restrictions this way rather than requesting broader filesystem access. Invoke the selected command on that scope with JSON output and a minimum cyclomatic complexity of 5. Keep the invocation, diagnostics, and findings as the baseline. Exit status 0 means the analysis succeeded, not that the complexity is acceptable. Resolve analysis failures before relying on the comparison.

3. Interpret every task-relevant finding:

   - Aim for CC 4 or lower.
   - Treat CC 5–6 as tolerable, not satisfactory. Actively look for a refactor that brings it to 4 or lower. Leave it unchanged only when available changes would not make the design easier to understand and change.
   - Treat CC 7 or higher as above the maximum. Reduce it to 6 or lower or meet the evidence standard under **When complexity above 6 may remain**.

4. Simplify before adding structure. Prefer removing decisions, states, nesting, and duplicated policy. Extract code or add an abstraction only when it creates a cohesive concept and makes the affected design easier to reason about as a whole.

5. Reject score-shifting. Do not distribute the same decisions across helpers or types, duplicate policy, obscure conditions, or change the reporting cutoff or targets. Measure every affected code unit.

6. Preserve behavior. Protect affected branch outcomes with tests. Run relevant tests and project checks, then rerun the same Knotbane invocation.

7. If branching moved beyond the original scope, compare the expanded scope against its pre-edit baseline. Otherwise, call the comparison incomplete.

## When complexity above 6 may remain

For each task-relevant code unit that remains above 6, establish that its branches form one cohesive responsibility that is clearer when kept together. Name the simplest plausible refactor. Using the actual code, show what it would duplicate, scatter, obscure, or couple. Domain complexity, legacy status, missing tests, and time constraints are not evidence that refactoring would worsen the code.

Keep this evaluation internal unless the user asks for the details. If a code unit remains above 6, report only its name, final complexity, and a one-sentence reason.

## Completion

Call the work complete only when:

- Successful, comparable Knotbane reports account for every task-relevant code unit.
- Every unit at CC 5–6 was actively evaluated, and every unit above 6 was reduced or meets the evidence standard above.
- Any refactor made the affected design simpler as a whole, and relevant tests and project checks pass.
- The final response briefly states the analysis scope, before-and-after result when code changed, the simplification or no-change outcome, and verification performed.

Otherwise, state what remains incomplete.
