# Requests
1. **get active users**
    - **URL:** http://../?activeUsers
    - **Parameters:** none
    - **Output:** {"others": <amountOtherTrackedMacs>,"users":[{"name":"<completeName>","time":"\<HH:MM>","pic":{"orga":"uploads\/logo\/<filename>","profile":"uploads\/profile\/<filename>"}}]}
    - **Description:** get all users where at least one mac address has been tracked in the last interval

2. **add new oganization**
    - **URL:** http://../?newOrganization
    - **Parameters:** post variables: "name" and "logo_url"
    - **Output:** "success"

3. **push tracked macs**
    - **URL:** http://../?pushMacs
    - **Parameters:** post variables: "macHash": "['hash1', 'hash2', ...]", "token"=<apiToken>
    - **Output:** "success"
    - **Description:** triggered by the pi, needs a security token as header "Authorization"

4. **add new users**
    - **URL:** http://../?newUser
    - **Parameters:** post variables: "first_name", "last_name", "orga_name", "profile_url", "mac1", "[mac2 -5]". if "orga_name" not in table then "orga_url" neccessary 
    - **Output:** "success"
    - **Description:** adds a new user to the database or update the entry if the user already exists

5. **get a list of all organizations**
    - **URL:** http://../?listOrganizations
    - **Parameters:** none
    - **Output:** ["organization1", "organization2", ....]
    - **Description:** for the select of the register form

6. **get all organizations**
    - **URL:** http://../?listTableOrga
    - **Parameters:** none
    - **Output:** table presence-tracker-orga as json
    - **Description:** for debugging

7. **get all macs**
    - **URL:** http://../?listTableMacs
    - **Parameters:** none
    - **Output:** table presence-tracker-macs as json
    - **Description:** for debugging

8. **get all users**
    - **URL:** http://../?listTableUsers
    - **Parameters:** none
    - **Output:** table presence-tracker-users as json
    - **Description:** for debugging
    
9. **get all macs that have been tracked in the last interval**
    - **URL:** http://../?activeMacs
    - **Parameters:** none
    - **Output:** [{"mac_hash":"e06bc74405124020328178d369da1d8d","user_id":<int/null>,"blacklisted":"<0/1>","first_seen":"<YYYY-MM-DD HH:MM:SS>","last_seen":"<YYYY-MM-DD HH:MM:SS>","here_since":"<YYYY-MM-DD HH:MM:SS>"}, ...]
    - **Description:** for debugging

# Structure of the Database
_for more information read the database dump 'launchpadapp.sql'_
1. **presence_tracker_macs**
	- mac_hash 		
	    - varchar(40)		
	    - PRIMARY
	- user_id		
	    - int(11) 		
	    - DEFAULT: NULL
	- blacklisted	
	    - tinyint(1)		
	    - DEFAULT: 0
	- first_seen	
	    - timestamp		
	    - DEFAULT: CURRENT_TIMESTAMP
	- last_seen		
	    - timestamp		
	    - DEFAULT: CURRENT_TIMESTAMP		
	    - ON UPDATE CURRENT_TIMESTAMP
	- here_since	
	    - timestamp		
	    - DEFAULT: CURRENT_TIMESTAMP

2. **presence_tracker_orga**
	- id 			
	    - int(11)			
	    - PRIMARY						
	    - AUTO_INCREMENT
	- name			
	    - varchar(40)		
	    - UNIQUE
	- img			
	    - varchar(50)
	
3. **presence_tracker_users**
	- id 
	    - int(11)
	    - PRIMARY		
	    - AUTO_INCREMENT
	- first_name
	    - varchar(30)
	- last_name	   
	    - varchar(30)
	- orga_id		
	    - int(11)			
	    - DEFAULT: NULL
	- profile_img	
	    - varchar(50)
	- created_at	
	    - timestamp		
	    - DEFAULT: CURRENT_TIMESTAMP
	
# TODO 
- set last_seen timeout from 10min to something bigger
- return status code on error and output json
- secure debug requests with token or set $default = false
- test if it is possible to choose "keine Organisation" (0) as new user