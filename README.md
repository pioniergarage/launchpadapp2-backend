# launchpadapp2-backend
The backend of the new generation launchpad app a coworking space management app logging opening times and more.

## Installation
- Fork the repo
- Clone it onto your pc
- Install dependencies with [Composer]
- Import database dump to your db
- Rename example.config.php to config.php and set db access values
- (production) set displayErrorDetails to false

## API Docs
**GET** `/openstate`

Tells you whether opened or closed.

Return: {"state":"[0/1]", "opensince":"[datetime]"}



**POST** `/openchange` (auth)

Triggers change of the open/close state. (Only every 3 seconds executable)

Require api key to be postet in the body: {"key": "[a key]"}

Return: {"state":"[success/error]", "changedTo":"[0/1/errorMessage]"}


**GET** `/opentoday`

Gives you all timeframes opened/closed today.

Return: [{"id":"885","open_at":"2017-11-17 23:11:17","close_at":"2017-11-18 00:11:36"}, ...]


**GET** `/openmonth`

Gives you all timeframes opened/closed in the last 30 days.

Return: [{"id":"885","open_at":"2017-11-17 23:11:17","close_at":"2017-11-18 00:11:36"}, ...]


**POST** `/addTrackedMac` (auth)

Tell backend about a recent tracked mac.

Require hash of mac to be posted in the body: {"macHash": "[a mac hash]"}

Return: "[success/fail]"


**GET** `/getRecentMacs` (deprecated)

Returns all tracked mac hashes.

Return: [{"macHash":"885","created_at":"2017-11-17 23:11:17","updated_at":"2017-11-17 23:13:36"}, ...]


**POST** `/user/newuser`

Register a new profile.

Require the following values to be included in the body: 
{"macHash": "[a mac hash]", "name":"[a name]", "role":"[a role]", "imageRef":"[imageRef]"}

Return: 200/400



**GET** `/user/activeUsers`

Returns user details of those users who have been tracked in the last 15min.

Return: [{"name":"[a name]","role":"[a role]","imageRef":"[an image ref]"}, ...]


## Private API access
Requests with (auth) are part of the private API. To use them you need to send an authorization key in the request header: {"Authorization": "[the key]"}.


[Composer]: <https://getcomposer.org/doc/00-intro.md>
