# 2×2 Table Input Guide

## New Logic (Updated Dec 7, 2025)

### Required Fields
- **A** (events in exposed group) - REQUIRED
- **C** (events in unexposed group) - REQUIRED

### Plus ONE of the following:
- **Option 1:** Provide **B and D** (no-event counts)
- **Option 2:** Provide **N1 and N0** (totals)

The system will automatically derive the rest!

## Table Structure

```
                 Outcome=Yes  Outcome=No   Total
Exposed (Rx)         A            B          N1
Unexposed (Ctrl)     C            D          N0
```

## Input Scenarios

### Scenario 1: You have A, C, B, D

**You provide:**
- A = 75
- C = 100
- B = 925
- D = 900

**System calculates:**
- N1 = A + B = 75 + 925 = 1000
- N0 = C + D = 100 + 900 = 1000

✅ **Use this when:** You know the exact event and no-event counts

---

### Scenario 2: You have A, C, N1, N0

**You provide:**
- A = 75
- C = 100
- N1 = 1000
- N0 = 1000

**System calculates:**
- B = N1 - A = 1000 - 75 = 925
- D = N0 - C = 1000 - 100 = 900

✅ **Use this when:** You know event counts and group totals

---

## Examples

### Example 1: RCT of Drug A vs Placebo

**From paper:** 
- Treatment group: 1000 patients, 75 had the outcome
- Control group: 1000 patients, 100 had the outcome

**Enter:**
- A = 75
- C = 100
- N1 = 1000
- N0 = 1000

**System derives:**
- B = 925
- D = 900

---

### Example 2: Cohort Study

**From paper:**
- Exposed: 386 events, 1987 non-events
- Unexposed: 502 events, 1869 non-events

**Enter:**
- A = 386
- C = 502
- B = 1987
- D = 1869

**System derives:**
- N1 = 2373
- N0 = 2371

---

## Validation Rules

The system checks:

1. ✅ **A and C must be provided**
2. ✅ **Either (B & D) or (N1 & N0) must be provided**
3. ❌ **Cannot provide both sets** (B & D AND N1 & N0)
4. ✅ **A ≤ N1** (events can't exceed total)
5. ✅ **C ≤ N0** (events can't exceed total)
6. ✅ **B ≥ 0** (derived value must be non-negative)
7. ✅ **D ≥ 0** (derived value must be non-negative)
8. ✅ **A + B = N1** (totals must match)
9. ✅ **C + D = N0** (totals must match)

## Error Messages

### "A (events in exposed) is required"
→ You must enter the number of events in the exposed group

### "C (events in unexposed) is required"
→ You must enter the number of events in the unexposed group

### "Provide either (B & D) or (N1 & N0) along with A & C"
→ After entering A and C, you need to provide either:
   - Both B and D, OR
   - Both N1 and N0

### "Provide either (B & D) or (N1 & N0), not both"
→ Choose ONE input method. Don't fill in all fields.

### "Derived B (N1 - A) is negative"
→ Your A value is larger than N1. Check your numbers.

### "Derived D (N0 - C) is negative"
→ Your C value is larger than N0. Check your numbers.

## Common Mistakes

### ❌ Mistake 1: Filling in all fields
```
A = 75, B = 925, C = 100, D = 900, N1 = 1000, N0 = 1000
```
**Error:** "Provide either (B & D) or (N1 & N0), not both"

**Fix:** Remove either B & D or N1 & N0

### ❌ Mistake 2: Only providing A and C
```
A = 75, C = 100
```
**Error:** "Provide either (B & D) or (N1 & N0) along with A & C"

**Fix:** Add either B & D or N1 & N0

### ❌ Mistake 3: A > N1
```
A = 1100, C = 100, N1 = 1000, N0 = 1000
```
**Error:** "A cannot exceed N1"

**Fix:** Check your numbers - events can't exceed total

## Quick Reference

| What you have | What to enter | System calculates |
|---------------|---------------|-------------------|
| Event counts + No-event counts | A, C, B, D | N1 = A+B, N0 = C+D |
| Event counts + Totals | A, C, N1, N0 | B = N1-A, D = N0-C |

## Tips

1. **Most common scenario:** Enter A, C, N1, N0 (if you have totals)
2. **From raw data:** Enter A, C, B, D (if you counted each cell)
3. **Double-check:** Make sure A ≤ N1 and C ≤ N0
4. **Leave empty:** Don't fill fields you want the system to calculate
5. **Required marker:** Fields with * are mandatory

## After Submission

The system will:
1. Validate your inputs
2. Derive missing values
3. Calculate metrics:
   - Re (Risk Exposed) = A / N1
   - Ru (Risk Unexposed) = C / N0
   - RR (Relative Risk) = Re / Ru
   - ARR (Absolute Risk Reduction) = Ru - Re
   - NNT (Number Needed to Treat) = 1 / ARR
4. Save the complete 2×2 table to database
5. Redirect to studies list

## Need Help?

See **USER_GUIDE.md** for complete workflow instructions.
