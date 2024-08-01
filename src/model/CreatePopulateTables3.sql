
alter session set nls_date_format = 'YYYY-MM-DD';

drop table Users cascade constraints;
drop table Locale cascade constraints;
drop table Languages cascade constraints;
drop table Environment cascade constraints;
drop table Destinations cascade constraints;
drop table Attractions cascade constraints;
drop table Itineraries cascade constraints;
drop table TextReviews cascade constraints;
drop table PhotoReviews cascade constraints;
drop table Photos cascade constraints;
drop table Include cascade constraints;

CREATE TABLE Users(
	userID	VARCHAR(50) PRIMARY KEY,
	email		VARCHAR(50) UNIQUE,
	firstName  	VARCHAR(50), 
	lastName   	VARCHAR(50));

CREATE TABLE Locale(
	countryName		VARCHAR(50) PRIMARY KEY,
	continent		VARCHAR(50),
	currency       	CHAR(3));

CREATE TABLE Languages(
	codeISO		CHAR(3) PRIMARY KEY,
	name			VARCHAR(50), 
	numOfSpeakers	INTEGER,
	originRegion     	VARCHAR(50),
	usageNotes      	VARCHAR(300));

CREATE TABLE Environment(
	cityName      VARCHAR(50),
	countryName   VARCHAR(50),
	climate       VARCHAR(500),
	codeISO       CHAR(3) NOT NULL,
	PRIMARY KEY (cityName, countryName),
	FOREIGN KEY (codeISO) REFERENCES Languages);

CREATE TABLE Destinations(
	destID		INTEGER PRIMARY KEY, 
	cityName		VARCHAR(50) NOT NULL,
	countryName  	VARCHAR(50) NOT NULL, 
	description    	VARCHAR(300),
	FOREIGN KEY (countryName) REFERENCES Locale
	ON DELETE CASCADE,
	FOREIGN KEY (cityName, countryName) REFERENCES Environment
	ON DELETE CASCADE);

CREATE TABLE Attractions(
	attractionID    INTEGER PRIMARY KEY,
	name            VARCHAR(50),
	description     VARCHAR(500),
	address         VARCHAR(300),
	destID          INTEGER NOT NULL,
	FOREIGN KEY (destID) REFERENCES Destinations
	ON DELETE CASCADE);

CREATE TABLE Itineraries(
	planID		INTEGER PRIMARY KEY, 
	duration		INTEGER,
	dateCreated		VARCHAR(50),
	destID		INTEGER NOT NULL,
	userID		VARCHAR(50) NOT NULL,
	FOREIGN KEY (destID) REFERENCES Destinations
	ON DELETE CASCADE,
	FOREIGN KEY (userID) REFERENCES Users
	ON DELETE CASCADE);

CREATE TABLE TextReviews(
	reviewID		INTEGER PRIMARY KEY, 
	rating		INTEGER, 
	title			VARCHAR(50), 
	publishDate		DATE,
	bodyText		VARCHAR(500),
	textLanguage    	VARCHAR(50),
	userID         	VARCHAR(50) NOT NULL,
	attractionID    	INTEGER NOT NULL,
	FOREIGN KEY (userID) REFERENCES Users
	ON DELETE CASCADE,
	FOREIGN KEY (attractionID) REFERENCES Attractions
	ON DELETE CASCADE,
	CHECK (rating >= 0 AND rating <= 10));

CREATE TABLE PhotoReviews(
	reviewID            INTEGER PRIMARY KEY, 
	rating              INTEGER, 
	title               VARCHAR(50), 
	publishDate         DATE,
	numOfPhotos         INTEGER,
	visibility          INTEGER,
	userID              VARCHAR(50) NOT NULL,
	attractionID        INTEGER NOT NULL,
	FOREIGN KEY (userID) REFERENCES Users
	ON DELETE CASCADE,
	FOREIGN KEY (attractionID) REFERENCES Attractions
	ON DELETE CASCADE,
	CHECK (rating >= 0 AND rating <= 10),
	CHECK (visibility IN (0, 1)));

CREATE TABLE Photos(
	photoID		INTEGER PRIMARY KEY, 
	caption         	VARCHAR(300), 
	photoURL      	VARCHAR(100) UNIQUE, 
	attractionID   	INTEGER NOT NULL,
	reviewID        	INTEGER NOT NULL,
	FOREIGN KEY (attractionID) REFERENCES Attractions
	ON DELETE CASCADE,
	FOREIGN KEY (reviewID) REFERENCES PhotoReviews
	ON DELETE CASCADE);

CREATE TABLE Include(
	attractionID	INTEGER,
	planID		INTEGER,
	visitDate		VARCHAR(50),
	PRIMARY KEY (attractionID, planID),
	FOREIGN KEY (attractionID) REFERENCES Attractions
	ON DELETE CASCADE,
	FOREIGN KEY (planID) REFERENCES Itineraries
	ON DELETE CASCADE);


INSERT INTO Users(userID, email, firstName, lastName)
VALUES ('dagu', 'dagu@email.com', 'David', 'Guo');

INSERT INTO Users(userID, email, firstName, lastName)
VALUES ('kev_mochi', 'kevz@email.com', 'Kevin', 'Zhou');

INSERT INTO Users(userID, email, firstName, lastName)
VALUES ('kev_megu', 'kp@email.com', 'Kevin', 'Poon');

INSERT INTO Users(userID, email, firstName, lastName)
VALUES ('squishyPancake', 'pancake@otheremail.com', 'Sophia', 'Martin');

INSERT INTO Users(userID, email, firstName, lastName)
VALUES ('trustme', 'trusttrust123@random.com', 'Olivia', 'Martin');



INSERT INTO Locale(countryName, continent, currency)
VALUES('Canada', 'North America', 'CAD');

INSERT INTO Locale(countryName, continent, currency)
VALUES('Japan', 'Asia', 'JPY');

INSERT INTO Locale(countryName, continent, currency)
VALUES('China', 'Asia', 'CNY');

INSERT INTO Locale(countryName, continent, currency)
VALUES('United States of America', 'North America', 'USD');

INSERT INTO Locale(countryName, continent, currency)
VALUES('United Kingdom', 'Europe', 'GBP');



INSERT INTO Languages(codeISO, name, numOfSpeakers, originRegion, usageNotes)
VALUES('eng', 'English', 604300000, NULL, NULL);

INSERT INTO Languages(codeISO, name, numOfSpeakers, originRegion, usageNotes)
VALUES('jpn', 'Japanese', 125100000, NULL, NULL);

INSERT INTO Languages(codeISO, name, numOfSpeakers, originRegion, usageNotes)
VALUES('kor', 'Korean', 75000000, NULL, NULL);

INSERT INTO Languages(codeISO, name, numOfSpeakers, originRegion, usageNotes)
VALUES('chi', 'Chinese', 1300000000, NULL, NULL);

INSERT INTO Languages(codeISO, name, numOfSpeakers, originRegion, usageNotes)
VALUES('jpk', 'Kansai Japanese', 125100000, 'Kansai region of Japan', 'Kansai-ben is primarily used in western part of Japan such as Osaka and Kyoto.');



INSERT INTO Environment(cityName, countryName, climate, codeISO)
VALUES('Vancouver', 'Canada', 'Sunny in the summer with temperature in the range of 20 to 30 degrees Celsius. Rainy during the rest of the year.', 'eng');

INSERT INTO Environment(cityName, countryName, climate, codeISO)
VALUES('Tokyo', 'Japan', 'Very hot and humid during the summer.', 'jpn');

INSERT INTO Environment(cityName, countryName, climate, codeISO)
VALUES('Orlando', 'United States of America', 'Tropical weather.', 'eng');

INSERT INTO Environment(cityName, countryName, climate, codeISO)
VALUES('Osaka', 'Japan', 'Hot and humid in the summer. Mild weather for the rest of the year.', 'jpk');

INSERT INTO Environment(cityName, countryName, climate, codeISO)
VALUES('Kyoto', 'Japan', 'Similar to Osaka. Hot and humid in the summer and mild weather for the rest of the year.', 'jpk');



INSERT INTO Destinations(destID, cityName, countryName, description)
VALUES(1, 'Kyoto', 'Japan', 'It is a historic city with lots of traditional architecture and temples.');

INSERT INTO Destinations(destID, cityName, countryName, description)
VALUES(2, 'Tokyo', 'Japan', 'Capital of Japan. It is a vibrant metropolis with busy city life.');

INSERT INTO Destinations(destID, cityName, countryName, description)
VALUES(3, 'Osaka', 'Japan', 'It is a big city in western part of Japan.');

INSERT INTO Destinations(destID, cityName, countryName, description)
VALUES(4, 'Vancouver', 'Canada', 'A city in the province British Columbia. Known for its integration of nature and city life.');

INSERT INTO Destinations(destID, cityName, countryName, description)
VALUES(5, 'Orlando', 'United States of America', 'Known for Disney World, Universal Studios and other attractions.');



INSERT INTO Attractions(attractionID, name, description, address, destID)
VALUES(1, 'Kiyomizu-dera', 'A famous temple in Kyoto.', '1 Chome-294 Kiyomizu, Higashiyama Ward, Kyoto, 605-0862, Japan', 1);

INSERT INTO Attractions(attractionID, name, description, address, destID)
VALUES(2, 'Tokyo Skytree', 'A famous tower in Tokyo', '1-chome-1-2 Oshiage, Sumida City, Tokyo 131-0045, Japan', 2);

INSERT INTO Attractions(attractionID, name, description, address, destID)
VALUES(3, 'Stanley Park', 'A big park in the downtown Vancouver area.', '1042 Stanley Park Dr, Vancouver, BC V6G 3E2', 4);

INSERT INTO Attractions(attractionID, name, description, address, destID)
VALUES(4, 'Universal Studios Osaka', 'A great attraction in Osaka', '2 Chome-1-33 Sakurajima, Konohana Ward, Osaka, 554-0031, Japan', 3);

INSERT INTO Attractions(attractionID, name, description, address, destID)
VALUES(5, 'Imperial Palace', 'This is where the Japanese Royal family lives.', '1-1 Chiyoda, Chiyoda City, Tokyo 100-8111, Japan', 2);


INSERT INTO Itineraries(planID, duration, dateCreated, destID, userID)
VALUES(1, 4, '2023-12-01', 3, 'kev_mochi');

INSERT INTO Itineraries(planID, duration, dateCreated, destID, userID)
VALUES(2, 3, '2023-01-25', 2, 'kev_mochi');

INSERT INTO Itineraries(planID, duration, dateCreated, destID, userID)
VALUES(3, 7, '2023-10-10', 5, 'kev_megu');

INSERT INTO Itineraries(planID, duration, dateCreated, destID, userID)
VALUES(4, 3, '2023-03-07', 3, 'kev_megu');

INSERT INTO Itineraries(planID, duration, dateCreated, destID, userID)
VALUES(5, 7, '2023-01-09', 2, 'dagu');


INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(1, 8, 'Visiting Tokyo Skytree', '01-SEP-23', 'Es muy lindo! Visitado con mis amigos :)', 'Spanish', 'kev_mochi', 2);

INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(2, 10, 'A Day at Universal Studios Osaka', '30-SEP-23', 'Many different things you can do there! Must visit!', 'English', 'kev_megu', 4);

INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(3, 9, 'Stanley Park on Canada Day', '01-JUL-23', 'There were a lot of people! Go early if you can!', 'English', 'trustme', 3);

INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(4, 3, 'Stanley Park Day Trip', '04-OCT-19', 'The weather was bad. Not a fun day.', 'English', 'squishyPancake', 3);

INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(5, 7, 'Kiyomizu-dera trip', '31-MAY-23', 'This is a famous attraction and so expect there to be a lot of people.', 'English', 'dagu', 1);

INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(6, 9, 'What a Wonderful Sight!', '30-MAY-23', 'Very busy during the day, I suggest going at night!.', 'English', 'dagu', 2);

INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(7, 7, 'Great Place for a Run', '23-AUG-22', 'Great scenery! Watch out for the bike lane.', 'English', 'dagu', 3);

INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(8, 3, 'REALLY EXPENSIVE!!', '28-MAY-23', 'Holy moly, I paid almost $200 dollars to wait 3 hours in line!', 'English', 'dagu', 4);

INSERT INTO TextReviews(reviewID, rating, title, publishDate, bodyText, textLanguage, userID, attractionID)
VALUES(9, 6, 'Pretty Good View From Afar', '29-MAY-23', 'No one told me you cannot enter the Imperial Palace... I should have known!', 'English', 'dagu', 5);


INSERT INTO PhotoReviews(reviewID, rating, title, publishDate, numOfPhotos, visibility, userID, attractionID)
VALUES(1, 8, 'Day Trip to Universal Studios Osaka', '01-SEP-23', 1, 1, 'kev_megu', 4);

INSERT INTO PhotoReviews(reviewID, rating, title, publishDate, numOfPhotos, visibility, userID, attractionID)
VALUES(2, 10, 'Visiting Tokyo Skytree', '31-AUG-23', 1, 1, 'kev_mochi', 2);

INSERT INTO PhotoReviews(reviewID, rating, title, publishDate, numOfPhotos, visibility, userID, attractionID)
VALUES(3, 10, 'A day at Kiyomizu-dera', '10-MAY-23', 1, 1, 'dagu', 1);

INSERT INTO PhotoReviews(reviewID, rating, title, publishDate, numOfPhotos, visibility, userID, attractionID)
VALUES(4, 6, 'Stanley Park Day Trip', '03-OCT-19', 1, 1, 'squishyPancake', 3);

INSERT INTO PhotoReviews(reviewID, rating, title, publishDate, numOfPhotos, visibility, userID, attractionID)
VALUES(5, 9, 'Stanley Park with Friends', '15-JUL-18', 1, 1, 'trustme', 3);


INSERT INTO Photos(photoID, caption, photoURL, attractionID, reviewID)
VALUES(1, 'Beautiful Day at Stanley Park with Emily and Johnny', 'https://lh3.googleusercontent.com/p/AF1QipM-Epa9xKpiiGJBHvjMrZ7FtprpMONj1k_q4ngL=s1360-w1360-h1020', 3, 5);

INSERT INTO Photos(photoID, caption, photoURL, attractionID, reviewID)
VALUES(2, 'At the entrance of Tokyo Skytree', 'https://lh3.googleusercontent.com/p/AF1QipMx2r2o_u3eBI7ZkdQiIhZFjJ--PdTqeqnjKrWN=s1360-w1360-h1020', 2, 2);

INSERT INTO Photos(photoID, caption, photoURL, attractionID, reviewID)
VALUES(3, 'Inside Universal Studios Osaka', 'https://lh5.googleusercontent.com/p/AF1QipNNdKJzRzJjxwItGsoiFtrx8K5DJ4BdQPO7pb5s=s0', 4, 1);

INSERT INTO Photos(photoID, caption, photoURL, attractionID, reviewID)
VALUES(4, 'At Kiyomizu-dera', 'https://lh3.googleusercontent.com/p/AF1QipOaqMTtvzlBA62QNVPRYv_KICLreT97nS04eMXY=s1360-w1360-h1020', 1, 3);

INSERT INTO Photos(photoID, caption, photoURL, attractionID, reviewID)
VALUES(5, 'Rainy Stanley Park', 'https://lh3.googleusercontent.com/p/AF1QipPZu9FEVz1lLXKrkJtA6flbf-Iv36QfswJrmKR-=s1360-w1360-h1020', 3, 4);



INSERT INTO Include(attractionID, planID, visitDate)
VALUES(2, 2, '2023-08-27');

INSERT INTO Include(attractionID, planID, visitDate)
VALUES(4, 4, '2023-07-01');

INSERT INTO Include(attractionID, planID, visitDate)
VALUES(2, 5, '2023-05-03');

INSERT INTO Include(attractionID, planID, visitDate)
VALUES(4, 1, '2023-08-25');

INSERT INTO Include(attractionID, planID, visitDate)
VALUES(5, 2, '2023-08-28');
