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
# setup/localy
copy the project into your XAMPP then htdocs folder(it have to be only there no where else otherwise it is not going to work)
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
Update cnfg.php with your DB name
then visit http://localhost/<project name>/welcome.html
# run
download the repo and xampp
THEN open http://localhost/apy/welcome.html

welcome.html=the welcoming page
index.php=Home page item grid, filters, post-item form
acc.php	Login/register page
login_register.php	Handles login/register form submissions
logout.php	logout
profile.php	View own profile 
cart.php	View items added to cart
save_item.php	save item, delete, toggle like, add comment, add/remove from cart 
upload_picture.php	Handles profile picture upload
cnfg.php	Database connection
script.js	
style.css	All styling
# not  built yet
No real payment, cart has no checkout flow 
