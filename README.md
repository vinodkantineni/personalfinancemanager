# Personal Finance Manager

A web application designed to help users analyze their financial transaction data by uploading Excel files and generating insightful, visual reports. The application features a secure two-factor authentication system and offers two distinct modes of data analysis for a flexible user experience.

## ✨ Key Features

* **Secure Two-Factor Authentication (2FA)**: User accounts are protected with a robust login system. After password verification, an OTP is sent to the user's email to complete the login.
* **Dual Data Analysis Modes**:
    * **Instant Client-Side Analysis**: Get immediate feedback with interactive charts generated directly in your browser using JavaScript, Chart.js, and SheetJS.
    * **Detailed Backend Analysis**: Generate a comprehensive, static HTML report with detailed plots using a powerful Python script with Pandas, Matplotlib, and Seaborn.
* **Secure File Management**: Users can upload their financial documents, which are securely stored and linked to their account. A secure viewer ensures that users can only access their own files.
* **User Registration & Management**: A complete signup system with secure password hashing (`PASSWORD_BCRYPT`) is included.
* **User Support System**:
    * [cite_start]An integrated chatbot provides answers to common questions[cite: 2].
    * A dedicated form allows users to report issues, which are then saved to the database for review.

## 💻 Technology Stack

* **Frontend**: HTML5, CSS3, JavaScript
* **Backend**: PHP, Python
* **Database**: MySQL
* **JavaScript Libraries**: Chart.js, SheetJS (xlsx.js)
* **PHP Libraries**: PHPMailer (for sending email OTPs)
* **Python Libraries**: Pandas, Matplotlib, Seaborn

## 💾 Database Schema

The application uses three main tables to manage its data:

1.  `users`: Stores user credentials and profile information, including a securely hashed password.
2.  `uploads`: Tracks all files uploaded by users, linking them to a `user_id`.
3.  `issue_reports`: Contains all the support tickets or issues reported by users through the contact form.

## 🚀 Getting Started

To set up the project locally, follow these steps:

1.  **Clone the Repository**
    ```bash
    git clone [https://github.com/vinodkantineni/personalfinancemanager.git](https://github.com/vinodkantineni/personalfinancemanager.git)
    cd personalfinancemanager
    ```
2.  **Database Setup**
    * Create a MySQL database named `ptf`.
    * Import the SQL schemas from the `Table creating code.txt` file to create the `users`, `uploads`, and `issue_reports` tables.
    * Update the database credentials in `db_connect.php`[cite: 3].

3.  **Backend Setup**
    * Ensure you have a local server environment like XAMPP or WAMP with PHP and MySQL.
    * Update the email credentials in `login.php` to use your own Gmail account with an app password for sending OTPs.
    * Install Python and the required libraries:
        ```bash
        pip install pandas matplotlib seaborn
        ```

4.  **Run the Application**
    * Place the project files in your web server's root directory (e.g., `htdocs` in XAMPP).
    * Open your web browser and navigate to the `welcome.html` page.
