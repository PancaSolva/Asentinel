# Replace Reverb with AJAX Polling for Dashboard Auto-Update

Status: In Progress

## Plan Summary
Replace real-time Reverb/Echo with periodic AJAX polling (every 20s) to `/api/admin/dashboard-stats` and `/monitoring-logs`.

## Steps
### 1. [x] Gather file info and plan ✅
### 2. [ ] Edit `resources/js/pages/Dashboard.jsx`: 
  - Remove Echo listener and newLogIds state.
  - Add polling useEffect with setInterval(20s).
### 3. [ ] (Optional) Edit `app/Jobs/CheckServiceJob.php`: Comment broadcasts.
### 4. [ ] (Optional) Edit `resources/js/bootstrap.js`: Comment Echo init.
### 5. [ ] Rebuild: `npm run dev`
### 6. [ ] Test: Open dashboard, verify polling in Network tab, manual check updates.
### 7. [ ] Complete task.

Next step: Edit Dashboard.jsx

