# Adding fixture data

You can use this workflow for identifying and capturing fixture changes:
- load existing fixture data by running `load-fixtures` at the studio command prompt
- Take a complete snapshot of the database `dump-sql > .scratch/snapshot.before-changes.sql`
- Create new records using the UI
- Take a complete snapshot `dump-sql > .scratch/snapshot.after-changes.sql`
- Open up the two .sql files in a visual diff viewer and manually transplant the added records over to their appropriate fixture files in the repo