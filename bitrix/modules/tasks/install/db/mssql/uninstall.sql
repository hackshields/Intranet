ALTER TABLE b_tasks DROP CONSTRAINT b_tasks_ibfk_2
GO
ALTER TABLE b_tasks DROP CONSTRAINT b_tasks_ibfk_3
GO
ALTER TABLE b_tasks DROP CONSTRAINT b_tasks_ibfk_4
GO
ALTER TABLE b_tasks DROP CONSTRAINT b_tasks_ibfk_5
GO
ALTER TABLE b_tasks DROP CONSTRAINT b_tasks_ibfk_6
GO
ALTER TABLE b_tasks_dependence DROP CONSTRAINT b_tasks_dependence_ibfk_1
GO
ALTER TABLE b_tasks_dependence DROP CONSTRAINT b_tasks_dependence_ibfk_2
GO
ALTER TABLE b_tasks_file DROP CONSTRAINT b_tasks_file_ibfk_1
GO
ALTER TABLE b_tasks_file DROP CONSTRAINT b_tasks_file_ibfk_2
GO
ALTER TABLE b_tasks_member DROP CONSTRAINT b_tasks_member_ibfk_1
GO
ALTER TABLE b_tasks_member DROP CONSTRAINT b_tasks_member_ibfk_2
GO
ALTER TABLE b_tasks_tag DROP CONSTRAINT b_tasks_tag_ibfk_1
GO
ALTER TABLE b_tasks_tag DROP CONSTRAINT b_tasks_tag_ibfk_2
GO
ALTER TABLE b_tasks_template DROP CONSTRAINT b_tasks_template_ibfk_1
GO
ALTER TABLE b_tasks_template DROP CONSTRAINT b_tasks_template_ibfk_2
GO
ALTER TABLE b_tasks_template DROP CONSTRAINT b_tasks_template_ibfk_3
GO
ALTER TABLE b_tasks_template DROP CONSTRAINT b_tasks_template_ibfk_4
GO
ALTER TABLE b_tasks_viewed DROP CONSTRAINT b_tasks_viewed_ibfk_1
GO
ALTER TABLE b_tasks_viewed DROP CONSTRAINT b_tasks_viewed_ibfk_2
GO
ALTER TABLE b_tasks_viewed DROP CONSTRAINT df_b_tasks_viewed_viewed_date
GO
ALTER TABLE b_tasks_elapsed_time DROP CONSTRAINT df_b_tasks_elapsed_time_created_date
GO
ALTER TABLE b_tasks_reminder DROP CONSTRAINT b_tasks_reminder_ibfk_1
GO
ALTER TABLE b_tasks_reminder DROP CONSTRAINT b_tasks_reminder_ibfk_2
GO
DROP TABLE b_tasks
GO
DROP TABLE b_tasks_dependence
GO
DROP TABLE b_tasks_file
GO
DROP TABLE b_tasks_member
GO
DROP TABLE b_tasks_tag
GO
DROP TABLE b_tasks_template
GO
DROP TABLE b_tasks_viewed
GO
DROP TABLE b_tasks_log
GO
DROP TABLE b_tasks_elapsed_time
GO
DROP TABLE b_tasks_reminder
GO
DROP TABLE b_tasks_filters
GO
