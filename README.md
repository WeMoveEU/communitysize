# Community Size API

CiviCRM Extension provides API for getting size of community.

## Assumptions

* Contact is member of group specified by settings on status `Added`,
* Contact is not deleted,
* Contact is not Opt-out,
* Contact is not deceased,
* Contact has email,
* Primary email is not hold.

## Settings

* `member_group_id` Membership of this group means that contact is in our community
* `activity_type_name` Activity type used for marking contacts who are removed from group, by default *Optout*

## API actions

* `getcount`
* `cleanup` cleaning up contacts from group, parameters
    * `group_id` default from settings
    * `limit` number of contacts per one call
