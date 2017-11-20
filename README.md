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



**POST** `/openchange`

Triggers change of the open/close state. (Only every 3 seconds executable)

Require api key to be postet in the body: {"key": "[a key]"}

Return: {"state":"[success/error]", "changedTo":"[0/1/errorMessage]"}


**GET** `/opentoday`

Gives you all timeframes opened/closed today.

Return: [{"id":"885","open_at":"2017-11-17 23:11:17","close_at":"2017-11-18 00:11:36"}, ...]


**GET** `/openmonth`

Gives you all timeframes opened/closed in the last 30 days.

Return: [{"id":"885","open_at":"2017-11-17 23:11:17","close_at":"2017-11-18 00:11:36"}, ...]





[Composer]: <https://getcomposer.org/doc/00-intro.md>
