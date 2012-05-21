# Upgrading

## v<next> ##

 * Run `sake dev/tasks/WorkflowMigrationTask_LatestCompletedWorkflowRequestID` in order to 
   populate the latest workflow request relationship for existing requests (in the `SiteTree_live` table).
   This is required e.g. for `PagesScheduledForDeletionReport` to correctly view data.