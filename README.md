# apy

What is the apy app?
A local marketplace for handmade crochet/knit creations — browse, post, like, comment, message sellers, and save items to a cart.
# features 
Auth: register/login 
Item listings: post items with an uploaded photo, category, country, price, description
Filters: search by title, category, country, max price
Likes: like/unlike any item
Comments: comment on any item
Cart: add/remove items to a personal cart and with running total
Profile
Delete: remove your own posted items
# tech
js html php SQL css
# setup
download the repo
install XAMPP 
in XAMPP active apache and my sql
go to localhost/phpmyadmin creat a databse 
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    country VARCHAR(255)
);

CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100),
    location VARCHAR(100),
    image VARCHAR(255),
    description TEXT,
    posted_by VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_like (item_id, user_email)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    item_id INT NOT NULL,
    UNIQUE KEY unique_cart_item (user_email, item_id)
);

then visit http://localhost/apy/welcome.html
