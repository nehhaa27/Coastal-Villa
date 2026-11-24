# Coastal Villa and Room Booking System

**Module:** Web Technologies – Continuous Assessment
**Project:** Dynamic room booking website for a Sri Lankan coastal villa (*Mirissa Sands Villa*, Mirissa)
**Student name:** Neha Aluthgedara  **Student ID:** 24IT0452

---

## 1. Project Overview

This is a dynamic web application that lets guests browse the rooms of a coastal villa, search for rooms that are free on their dates, register, log in, make booking requests and manage their own bookings. The villa administrator can manage rooms and confirm or cancel bookings.

## 2. Technologies Used

| Layer | Technology |
|---|---|
| Structure | HTML5 |
| Styling | CSS3 and Bootstrap 5.3 (loaded from a CDN) |
| Client-side logic | Vanilla JavaScript (no libraries) |
| Server-side logic | PHP (procedural, MySQLi with prepared statements) |
| Database | MySQL (phpMyAdmin) |
| Local server | XAMPP (Apache + MySQL) |

## 3. Features

### Guest features
- **Home page:** villa name, introduction, photo gallery, coastal location highlights, main facilities, navigation bar and "Book Now" buttons.
- **Rooms page:** room cards with picture, description, facilities, maximum guests, price per night and a **Book** button. A **View Details** button opens a pop-up with the full room details.
- **Availability search:** filter by check-in date, check-out date and number of guests. Rooms that already have an overlapping *Confirmed* booking are hidden.
- **Registration and login:** guest accounts using PHP sessions; passwords are stored hashed.
- **Booking form:** shows the guest's details, room selection and dates. JavaScript blocks past or invalid dates and calculates the number of nights and total price on screen.
- **My Bookings:** a guest sees only their own bookings with their status (Pending, Confirmed, Cancelled) and can cancel a *Pending* booking.

### Admin features (admin accounts only)
- **Dashboard** with summary numbers.
- **Room management:** add, view, edit, activate/deactivate and delete rooms. A room that already has bookings cannot be deleted, only deactivated.
- **Booking management:** view all bookings with guest details, filter by status, and mark bookings as *Confirmed* or *Cancelled*.

### Responsive design
Bootstrap's grid and navigation bar adapt the layout to desktop, tablet and mobile screens. A CSS media query in `css/style.css` adds extra changes for phones.

## 4. Database Design

Database name: `coastal_villa` (created by `db_structure.sql`)

**users**
| Column | Description |
|---|---|
| user_id (PK) | Auto-increment ID |
| name | Full name |
| email | Unique email address |
| contact_number | Phone number |
| password | Hashed password |
| role | `guest` or `admin` |

**rooms**
| Column | Description |
|---|---|
| room_id (PK) | Auto-increment ID |
| room_name | Name of the room |
| room_type | e.g. Deluxe, Family, Cabana |
| description | Text; may end with `Facilities: ...` |
| price_per_night | Price in LKR |
| max_guests | Maximum number of guests |
| room_status | `available` or `unavailable` |

**bookings**
| Column | Description |
|---|---|
| booking_id (PK) | Auto-increment ID |
| user_id (FK → users) | The guest who booked |
| room_id (FK → rooms) | The booked room |
| check_in, check_out | Stay dates |
| total_amount | Total price calculated by the server |
| booking_status | `Pending`, `Confirmed` or `Cancelled` |
| booking_date | When the booking was made |

**Relationships:** one user can have many bookings, and one room can have many bookings (two foreign keys in `bookings`).

## 5. Setup Instructions (XAMPP on Windows)

1. Copy the `coastal_villa` folder into `C:\xampp\htdocs\`.
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin`, click the **Import** tab, choose `db_structure.sql` and click **Go**. This creates the database, the three tables and the sample data.
4. Open `http://localhost/coastal_villa/` in a browser.

If your MySQL root user has a password, change `$db_pass` in `db.php`.

## 6. Sample Login Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@coastalvilla.lk | admin123 |
| Guest | guest@example.com | guest123 |

New guests can also register from the **Register** page.

## 7. Folder and File Structure

```
coastal_villa/
├── db_structure.sql      Database tables + sample data (1 admin, 1 guest, 4 rooms)
├── db.php                Database connection, timezone, session start, date helper
│
├── index.php             Home page
├── rooms.php             Rooms list, availability search, "View Details" pop-up
├── book.php              Booking form (login required)
├── process_booking.php   Validates and saves a booking on the server
├── my_bookings.php       Guest's own bookings (login required)
├── cancel_booking.php    Guest cancels a Pending booking
├── register.php          Guest registration
├── login.php             Login
├── logout.php            Logout
│
├── admin/                ADMIN ONLY
│   ├── index.php         Dashboard
│   ├── rooms.php         View rooms, activate / deactivate / delete
│   ├── room_form.php     Add a room / edit a room
│   ├── bookings.php      View all bookings, confirm or cancel
│   └── admin_nav.php     Admin menu
│
├── includes/
│   ├── header.php        <head>, Bootstrap CSS, navigation bar, message boxes
│   ├── footer.php        Footer and scripts
│   └── admin_check.php   Blocks anyone who is not an admin
│
├── css/style.css         Own styles and a media query
├── js/
│   ├── rooms.js          Blocks past dates in the search form
│   ├── booking.js        Date blocking, live nights/total, booking form checks
│   └── register.js       Registration form checks and live password match
└── images/               Room and gallery pictures (placeholder .svg files)
```

## 8. How the Main Parts Work

- **Shared layout:** every page includes `includes/header.php` and `includes/footer.php`, so the navigation bar and footer are written only once.
- **Availability search (`rooms.php`):** two stays overlap when `old_check_in < new_check_out AND old_check_out > new_check_in`. The query hides any room with a *Confirmed* booking that overlaps the chosen dates, and any room that cannot hold the number of guests.
- **Booking form (`book.php` + `js/booking.js`):** JavaScript sets the minimum dates, counts the nights, multiplies by the room price and shows the total using DOM manipulation.
- **Booking processing (`process_booking.php`):** the server re-checks the dates, guest count and room, recalculates the total price itself (the price from the browser is never trusted), rejects overlapping confirmed dates and saves the booking as *Pending*.
- **Admin confirmation (`admin/bookings.php`):** before confirming, the overlap check is run again so two confirmed bookings can never share the same dates.

## 9. Validation and Security

| Concern | How it is handled |
|---|---|
| Password storage | `password_hash()` when registering, `password_verify()` when logging in |
| SQL injection | Prepared statements (`mysqli_prepare`) wherever user input is used |
| Cross-site scripting (XSS) | Output is escaped with `htmlspecialchars()` |
| Session login | `$_SESSION` stores user id, name and role; the session id is regenerated at login |
| Admin access | `includes/admin_check.php` allows only `role == 'admin'` |
| Other guests' bookings | Queries include `user_id = ?`, so guests can only see and cancel their own bookings |
| Double-booking | Checked when a booking is made and again when the admin confirms it |
| Price tampering | Total price is recalculated on the server |
| Form validation | JavaScript (user-friendly) and PHP (the real check) for registration, search and booking |
| Role tampering | New registrations are always saved with the role `guest` |

## 10. Suggested Test Walkthrough

1. Register a new guest and log in.
2. On the Rooms page, search for dates and a number of guests, then click **Book**.
3. Submit the booking and check **My Bookings** for a *Pending* booking; try cancelling one.
4. Log in as the admin and confirm a booking in **Manage Bookings**.
5. Search the same dates again as a guest: the confirmed room should be hidden.
6. As a guest, try opening `http://localhost/coastal_villa/admin/index.php`: access should be denied.
7. In **Manage Rooms**, add, edit and deactivate a room, and check the Rooms page.

## 12. Limitations and Possible Improvements

- No online payment; bookings are requests that the admin confirms.
- No email notifications to guests.
- Rooms use one picture each; the database does not store image names.
- Guests cannot edit their profile or change their password.
