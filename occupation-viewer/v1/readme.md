# Requests
1. **toggle open state (opened/closed)**
    - **URL:** http://../?toggleStatus
    - **Parameters:** "token" as push variable
    - **Output:** {"status": "opened"} or {"status": "closed"}
    - **Description:** triggered by the arduino, needs a security token, deletes database entries where the opening time was < 10min

1. **toggle open state (opened/closed)**
    - **URL:** http://../?changeStatus=<open/close>
    - **Parameters:** "token" as push variable, value for the get variable "changeStatus"
    - **Output:** {"success": <true/false>}
    - **Description:** triggered by the arduino, needs a security token, deletes database entries where the opening time was < 10min, does not change if status is already in the given state

3. **get the current status of the traffic light**
    - **URL:** http://../?currentStatus
    - **Parameters:** none
    - **Output:** {"status": "<opened/closed>", "time": "<YYYY-MM-DD HH:MM:SS>"}
    - **Description:** for the traffic light on the website

4. **get all entrys of the table**
    - **URL:** http://../?listTable
    - **Parameters:** none
    - **Output:** database table as json
    - **Description:** for debugging, get all rows of the table occupation-viewer 

5. **get all opening times in the past of a given interval**
    - **URL:** http://../?history
    - **Parameters:** get variable: history=<days>, default value is 7 days
    - **Output:** [{"opened_at":"<YYYY-MM-DD HH:MM:SS>", "closed_at":"<YYYY-MM-DD HH:MM:SS>"}, ...]
    - **Description:** for the website as well 


# Structure of the Database
_for more information read the database dump 'launchpadapp.sql'_
1. **occupation_viewer**
	- id 			
        - int(11)			
        - PRIMARY						
        - AUTO_INCREMENT
    - opened_at			
        - timestamp		
        - UNIQUE
    - closed_at			
        - timestamp

# TODO 
- return status code on error and output json
- secure debug requests with token or set $default = false