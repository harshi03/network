<div align="center">
	<h1> REST Api with Slim PHP </h1>	
</div>

<div align="center">
	<a href="#changelog">
		<img src="https://img.shields.io/badge/stability-stable-green.svg" alt="Status">
	</a>
	<a href="#changelog">
		<img src="https://img.shields.io/badge/release-v1.0.0.2-blue.svg" alt="Version">
	</a>
	<a href="#changelog">
		<img src="https://img.shields.io/badge/update-march-yellowgreen.svg" alt="Update">
	</a>
	<a href="#license">
		<img src="https://img.shields.io/badge/license-MIT%20License-green.svg" alt="License">
	</a>
</div>


This is a simple REST Web Service which allow:

  * Register a user
  * Login a user
  * List a user details
  * Delete a specific message by its id
  * [Postman]: https://documenter.getpostman.com/collection/view/1588907-4f9504cf-f153-e7d2-2bd6-5134643715c8

<a name="started"></a>
## :traffic_light: Getting Started

This page will help you get started with this API.

<a name="requirements"></a>
### Requirements

  * PHP 7.1
  * MySQL or MariaDB
  * Apache Server

<a name="installation"></a>
### Installation

#### Create a database

Run the following SQL script

```SQL
-- --------------------------------------------------------
-- Database: `network`
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `network` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `network`;

-- --------------------------------------------------------
-- Table structure for table `codes`
-- --------------------------------------------------------
CREATE TABLE `codes` (
  `id_code` int(10) UNSIGNED NOT NULL,
  `type` enum('VIP','VVIP') NOT NULL,
  `value` varchar(10) NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `districts`
-- --------------------------------------------------------
CREATE TABLE `districts` (
  `id_district` int(10) UNSIGNED NOT NULL,
  `iso` varchar(2) NOT NULL,
  `district` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `guid` varchar(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` date NOT NULL,
  `id_district` int(10) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `mobile` text NOT NULL,
  `email` varchar(20) NOT NULL,
  `age` int(10) NOT NULL,
  `gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `pan_card` varchar(20) NOT NULL,
  `total_vehicle` int(10) NOT NULL,
  `total_male` int(10) NOT NULL,
  `total_female` int(10) NOT NULL,
  `type` varchar(10) NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `user_codes`
-- --------------------------------------------------------
CREATE TABLE `user_codes` (
  `id_user_code` int(10) UNSIGNED NOT NULL,
  `id_code` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `user_vehicles`
-- --------------------------------------------------------
CREATE TABLE `user_vehicles` (
  `id_user_vehicle` int(10) UNSIGNED NOT NULL,
  `id_vehicle` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `vehicles`
-- --------------------------------------------------------
CREATE TABLE `vehicles` (
  `id_vehicle` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Indexes for table `codes`
-- --------------------------------------------------------
ALTER TABLE `codes`
  ADD PRIMARY KEY (`id_code`),
  ADD UNIQUE KEY `value` (`value`),
  ADD KEY `type` (`type`);

-- --------------------------------------------------------
-- Indexes for table `districts`
-- --------------------------------------------------------
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id_district`);

-- --------------------------------------------------------
-- Indexes for table `users`
-- --------------------------------------------------------
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `id_user_UNIQUE` (`id_user`),
  ADD UNIQUE KEY `user_UNIQUE` (`username`),
  ADD UNIQUE KEY `guid_UNIQUE` (`guid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_countries1_idx` (`id_district`);

-- --------------------------------------------------------
-- Indexes for table `user_codes`
-- --------------------------------------------------------
ALTER TABLE `user_codes`
  ADD PRIMARY KEY (`id_user_code`),
  ADD KEY `fk_users_codes1` (`id_code`) USING BTREE,
  ADD KEY `fk_users_users1` (`id_user`) USING BTREE;

-- --------------------------------------------------------
-- Indexes for table `user_vehicles`
-- --------------------------------------------------------
ALTER TABLE `user_vehicles`
  ADD PRIMARY KEY (`id_user_vehicle`),
  ADD KEY `id_vehicle` (`id_vehicle`),
  ADD KEY `id_user` (`id_user`);

-- --------------------------------------------------------
-- Indexes for table `vehicles`
-- --------------------------------------------------------
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id_vehicle`);

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `codes`
-- --------------------------------------------------------
ALTER TABLE `codes`
  MODIFY `id_code` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `districts`
-- --------------------------------------------------------
ALTER TABLE `districts`
  MODIFY `id_district` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `users`
-- --------------------------------------------------------
ALTER TABLE `users`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `user_codes`
-- --------------------------------------------------------
ALTER TABLE `user_codes`
  MODIFY `id_user_code` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `user_vehicles`
-- --------------------------------------------------------
ALTER TABLE `user_vehicles`
  MODIFY `id_user_vehicle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `vehicles`
-- --------------------------------------------------------
ALTER TABLE `vehicles`
  MODIFY `id_vehicle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Constraints for table `users`
-- --------------------------------------------------------
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_countries1` FOREIGN KEY (`id_district`) REFERENCES `districts` (`id_district`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- --------------------------------------------------------
-- Constraints for table `user_codes`
-- --------------------------------------------------------
ALTER TABLE `user_codes`
  ADD CONSTRAINT `user_codes_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_codes_ibfk_2` FOREIGN KEY (`id_code`) REFERENCES `codes` (`id_code`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- --------------------------------------------------------
-- Constraints for table `user_vehicles`
-- --------------------------------------------------------
ALTER TABLE `user_vehicles`
  ADD CONSTRAINT `user_vehicles_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_vehicles_ibfk_2` FOREIGN KEY (`id_vehicle`) REFERENCES `vehicles` (`id_vehicle`) ON DELETE NO ACTION ON UPDATE NO ACTION;

```

#### Copy this project

  1. Clone or Download this repository
  2. Unzip the archive if needed
  3. Copy the folder in the htdocs dir
  4. Start a Text Editor (Atom, Sublime, Visual Studio Code, Vim, etc)
  5. Add the project folder to the editor

#### Install the project

  1. Go to htdocs dir

  * Windows

```bash
$ cd /d C:\xampp\htdocs
```

  * Linux

```bash
$ cd /opt/lampp/htdocs
```

  * MAC

```bash
$ cd applications/mamp/htdocs
```

  2. Go to the project folder

```bash
$ cd REST-Api-with-Slim-PHP
```

  3. Install with composer

```bash
$ composer install
```

    Or

```bash
$ php composer.phar install  
```

<a name="deployment"></a>
## :package: Deployment

<div align="center">
	<h3> Database Schema </h3>
	<a href="#installation">
		<img src="https://github.com/harshi03/network/blob/master/localhost%20%20%20localhost%20%20%20network%20%20%20phpMyAdmin%204%207%202.png?raw=true" alt="schema">
	</a>
</div>

<a name="built"></a>
## :wrench: Built With

  * XAMPP ([XAMPP for Windows 5.6.32](https://www.apachefriends.org/download.html))
  * ATOM ([ATOM](https://atom.io/))
  * COMPOSER ([COMPOSER](https://getcomposer.org/))
  * Postman Extension for Chrome [Postman]

<a name="test"></a>
## :100: Running the tests

Use RestEasy or Postman app for testing.

For authentication you can generate a new JSON Web Token with the url login.

Put the token on an HTTP header called Authorization. e.g.:

  * Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ

<div align="center">
	<img src="https://mfgil.files.wordpress.com/2017/12/header.png" alt="Header">
</div>

Put the parameters on a Query Parameter.

<div align="center">
	<img src="https://mfgil.files.wordpress.com/2017/12/test.png" alt="Test">
</div>

<a name="changelog"></a>
## :information_source: Changelog

**1.0.0.2** (03/29/2018)

  * <table border="0" cellpadding="4">
		<tr>
			<td>
				<strong>Language:</strong>
			</td>
			<td>
				PHP
			</td>
		</tr>
		<tr>
			<td><strong>
				Requirements:
			</strong></td>
			<td>
				<ul>
					<li>
						PHP 7.1
					</li>
					<li>
						MySQL or MariaDB 
					</li>
					<li>
						Apache Server
					</li>
				</ul>
			</td>
		</tr>
		
	</table>

<a name="license"></a>
## :memo: License

This API is licensed under the MIT License - see the
 [MIT License](https://opensource.org/licenses/MIT) for details.
 
 ## :response: Response Format
 
 This API is strictly following https://github.com/adnan-kamili/rest-api-response-format/blob/master/README.md
