# Phase 3 Isolated Populated Visual Preview

**Purpose:** Render populated Homepage and Shop evidence without changing persistent `rhythm_db`.

## Safety Contract

- Script: `tools/start-phase3-visual-preview.bat`
- Database: `storage/app/phase3-visual-fixture.sqlite` only
- The fixture path is ignored by Git.
- Database environment overrides exist only inside the script process.
- The normal project `.env` is not edited.
- Before any destructive migration command, the script asks Laravel for its effective connection and database path and aborts unless they match `sqlite|<exact fixture path>`.
- `migrate:fresh --seed` runs only after that guard passes.
- Closing the preview terminal restores normal operation; it does not change the project's configured MySQL database.

## Owner Steps

1. Pull the latest `rhythm-uat` branch.
2. Start Laragon.
3. Double-click `tools/start-phase3-visual-preview.bat`, or run it from Laragon Terminal.
4. Wait for `Populated preview is ready`.
5. Open the two printed local URLs and capture Homepage and Shop at 1440/DPR2 and 390/DPR2.
6. Close the terminal after capture.

A valid populated Shop capture visibly contains category shortcuts, a filter sidebar/drawer, a result count and product cards. If it says “The catalogue is being prepared,” it is the normal empty `rhythm_db` site rather than this isolated preview.
