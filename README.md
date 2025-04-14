# PHP Docker Project

A simple PHP 8 project using Docker with MySQL and Nginx.

## Components

- PHP 8.0 with essential extensions
- MySQL 8.0
- Nginx webserver

## Directory Structure

```
.
├── docker-compose.yml
├── Dockerfile
├── nginx
│   └── conf.d
│       └── app.conf
└── src
    └── index.php
```

## Setup Instructions

1. Make sure you have Docker and Docker Compose installed on your system.

2. Clone this repository:
   ```
   git clone <repository-url>
   cd <repository-directory>
   ```

3. Start the Docker containers:
   ```
   docker-compose up -d
   ```

4. Wait for the containers to start up (this may take a minute or two for the first run).

5. Access the application in your web browser:
   ```
   http://localhost
   ```

## Database Connection

The application is configured with the following database settings:

- Host: db
- Database: php_app
- Username: app_user
- Password: secret
- Root Password: root

You can connect to MySQL from your host machine using:
```
Host: localhost
Port: 3306
Username: app_user or root
Password: secret or root
```

## Stopping the Application

To stop the containers:
```
docker-compose down
```

To stop and remove all containers, networks, and volumes:
```
docker-compose down -v
``` 