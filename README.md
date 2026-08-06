# Geofencing Attendance System

A PHP/MySQL attendance management system that uses geofencing and browser geolocation to validate employee check-ins and check-outs.

## Features

- Employee check-in and check-out based on geolocation
- Geo-fencing support with Google Maps or browser geolocation API
- Admin panel for employee, location, project, holiday, and attendance management
- Monthly, daily, and period attendance reports
- Message center and profile management

## Requirements

- PHP 7.x or 8.x
- MySQL / MariaDB
- Apache (XAMPP or similar)
- Browser with geolocation support

## Setup

1. Copy the project files to your web server root directory (for example, `htdocs` in XAMPP).
2. Import the SQL files into your database:
   - `location.sql`
   - `shift_settings.sql`
3. Update database configuration in `db.php` and `admin/db.php` if needed.
4. Open the project in your browser, for example: `http://localhost/geofencing attendace/`

## Notes

- Make sure the database user has proper permissions for the project database.
- If you want to use Google Maps functionality, configure your API key in the relevant files.
- The main geofencing logic is handled in `checkDistance.js`.

## Repository

https://github.com/ezerahailu4-boop/geofencing-attendance-system
