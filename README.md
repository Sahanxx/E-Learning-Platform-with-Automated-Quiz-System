# E-Learning Platform with Automated Quiz System

## Project Description

This E-Learning Platform is a comprehensive web application designed to facilitate online learning with an integrated automated quiz system. It provides functionalities for users (students and administrators) to manage learning materials, take quizzes, and interact with a chatbot for assistance. The platform aims to offer an interactive and engaging learning experience, leveraging modern web technologies and AI integration for enhanced user support.

## Features

-   **User Authentication:** Secure login and registration for both students and administrators, ensuring role-based access to different parts of the system.
-   **Role-Based Access Control:** Distinct functionalities and dashboards for administrators (e.g., managing content, creating quizzes) and students (e.g., viewing notes, taking quizzes).
-   **Lecture Note Management:** Administrators can easily upload, organize, and manage lecture notes, typically in PDF format. Students can then access and view these notes directly within the platform.
-   **Automated Quiz System:**
    -   **Quiz Creation:** Administrators have the capability to create new quizzes, linking them to specific lecture notes or topics.
    -   **Question and Option Management:** Questions can be added to quizzes, along with multiple-choice options, and the correct answer can be designated.
    -   **Quiz Taking:** Students can take assigned quizzes, and their responses are recorded.
    -   **Automated Scoring:** The system automatically scores quizzes upon submission, providing immediate feedback to students.
    -   **Score Tracking:** Student scores are stored in the database, allowing for progress tracking and performance analysis.
-   **Interactive Chatbot:** An AI-powered chatbot is integrated into the platform to provide instant support and answer user queries. This chatbot leverages the OpenRouter API (specifically the DeepSeek model) to offer intelligent responses related to course content, platform usage, or general study tips.
-   **User Dashboards:**
    -   **Student Dashboard:** Provides students with an overview of their courses, accessible lecture notes, pending quizzes, and performance metrics.
    -   **Administrator Dashboard:** Offers administrators tools for content management, user management, quiz creation, and system monitoring.
-   **File Uploads:** Supports the secure upload of lecture notes (PDFs) to the server.
-   **Responsive Design:** The platform is built with a responsive design approach (utilizing Bootstrap), ensuring optimal viewing and interaction across various devices, including desktops, tablets, and mobile phones.

## Technologies Used

This project is built using a combination of popular web development technologies:

-   **Frontend:**
    -   **HTML5:** For structuring the web content.
    -   **CSS3:** For styling and layout, including custom styles and a modern, glassmorphism-inspired design.
    -   **Bootstrap 5.3:** A powerful front-end framework for responsive and mobile-first development, providing pre-built components and a grid system.
    -   **JavaScript:** For interactive elements, form validation, and dynamic content updates.
    -   **Font Awesome:** For scalable vector icons.
    -   **Google Fonts (Inter, Space Grotesk):** For modern and readable typography.

-   **Backend:**
    -   **PHP:** The server-side scripting language used for handling business logic, database interactions, and API integrations.
    -   **Composer:** PHP dependency manager, used for managing external libraries like `vlucas/phpdotenv`.
    -   **`vlucas/phpdotenv`:** A PHP library to load environment variables from a `.env` file, enhancing security by keeping sensitive information out of the codebase.

-   **Database:**
    -   **MySQL:** A robust relational database management system used to store all application data, including user information, lecture notes, quiz questions, options, student answers, and chatbot logs.

-   **API Integration:**
    -   **OpenRouter API:** Utilized for the intelligent chatbot functionality, connecting to various large language models (specifically `deepseek/deepseek-chat:free` in this implementation) to provide AI-driven responses.

## Installation

To set up and run this E-Learning Platform project locally, follow these detailed steps:

1.  **Clone the repository:**
    First, clone the project repository from GitHub to your local machine using Git:
    ```bash
    git clone <repository_url>
    ```
    Replace `<repository_url>` with the actual URL of your GitHub repository (e.g., `https://github.com/your-username/elearning-platform.git`).

2.  **Set up a web server environment:**
    This project requires a web server environment that supports PHP and MySQL. Popular choices include:
    -   **XAMPP:** (Windows, macOS, Linux) A free and open-source cross-platform web server solution stack package developed by Apache Friends, consisting mainly of the Apache HTTP Server, MariaDB (a MySQL fork), and interpreters for scripts written in the PHP and Perl programming languages.
    -   **WAMP:** (Windows) Windows Apache MySQL PHP.
    -   **LAMP:** (Linux) Linux Apache MySQL PHP.
    -   **MAMP:** (macOS) macOS Apache MySQL PHP.

    Ensure you have one of these environments installed and configured on your system.

3.  **Place project files:**
    After cloning, locate the `elearning_platform` folder. Copy this entire folder into your web server's document root directory. Common locations are:
    -   For XAMPP: `C:\xampp\htdocs\` (on Windows) or `/Applications/XAMPP/htdocs/` (on macOS).
    -   For WAMP: `C:\wamp\www\`.
    -   For LAMP/MAMP: The equivalent `htdocs` or `www` directory.

    Example: If you are using XAMPP on Windows, the path to your project will be `C:\xampp\htdocs\elearning_platform`.

4.  **Install Composer dependencies:**
    This project uses Composer to manage its PHP dependencies. You need to install Composer if you haven't already. Then, navigate to the `elearning_platform` directory in your terminal or command prompt and run the following command:
    ```bash
    cd C:\xampp\htdocs\elearning_platform  # Adjust path as per your setup
    composer install
    ```
    This command will download and install all the required PHP libraries, including `vlucas/phpdotenv`, into the `vendor/` directory.

## Database Setup

1.  **Create a MySQL database:**
    Open your preferred MySQL client (e.g., phpMyAdmin, MySQL Workbench, or the command line).
    -   If using phpMyAdmin, typically accessible via `http://localhost/phpmyadmin` in your web browser.
    -   Create a new database. The project expects the database to be named `elearning_platform`. You can do this by executing the SQL command `CREATE DATABASE elearning_platform;` or by using the graphical interface.

2.  **Import the database schema:**
    The database schema and initial data are provided in the `elearning_platform.sql` file, located directly within the `elearning_platform/` project directory.
    Import this SQL file into the `elearning_platform` database you just created. In phpMyAdmin, you can typically do this by selecting the database, going to the 

 `Import` tab, and uploading the `elearning_platform.sql` file.

    This SQL file contains the schema for the following tables:

    ### Database Schema Overview:

    **`user` table:** Stores user authentication and role information.
    | Column       | Type         | Description                                   |
    | :----------- | :----------- | :-------------------------------------------- |
    | `UserID`     | `INT`        | Primary Key, Auto-increment                   |
    | `Username`   | `VARCHAR(50)`| Unique username for login                     |
    | `Email`      | `VARCHAR(100)`| Unique email address for login                |
    | `Password`   | `VARCHAR(255)`| Hashed password (using `password_hash`)       |
    | `Role`       | `ENUM(\'admin\',\'student\')`| User role (`admin` or `student`)              |
    | `Created_At` | `TIMESTAMP`  | Timestamp of user creation                    |

    **`lecture_note` table:** Stores details about uploaded lecture notes.
    | Column        | Type         | Description                                   |
    | :------------ | :----------- | :-------------------------------------------- |
    | `NoteID`      | `INT`        | Primary Key, Auto-increment                   |
    | `UserID`      | `INT`        | Foreign Key referencing `user.UserID` (uploader)|
    | `Title`       | `VARCHAR(100)`| Title of the lecture note                     |
    | `File_Path`   | `VARCHAR(255)`| Path to the uploaded PDF file on the server   |
    | `Upload_Date` | `TIMESTAMP`  | Timestamp of upload                           |

    **`quiz` table:** Stores information about quizzes.
    | Column        | Type         | Description                                   |
    | :------------ | :----------- | :-------------------------------------------- |\n    | `QuizID`      | `INT`        | Primary Key, Auto-increment                   |
    | `NoteID`      | `INT`        | Foreign Key referencing `lecture_note.NoteID` (optional link to notes)|
    | `Quiz_Title`  | `VARCHAR(100)`| Title of the quiz                             |
    | `Creation_Date`| `TIMESTAMP`  | Timestamp of quiz creation                    |

    **`question` table:** Stores individual quiz questions.
    | Column         | Type         | Description                                   |
    | :------------- | :----------- | :-------------------------------------------- |
    | `QuestionID`   | `INT`        | Primary Key, Auto-increment                   |
    | `QuizID`       | `INT`        | Foreign Key referencing `quiz.QuizID`         |
    | `Question_Text`| `TEXT`       | The full text of the question                 |

    **`option` table:** Stores multiple-choice options for quiz questions.
    | Column       | Type         | Description                                   |
    | :----------- | :----------- | :-------------------------------------------- |
    | `OptionID`   | `INT`        | Primary Key, Auto-increment                   |
    | `QuestionID` | `INT`        | Foreign Key referencing `question.QuestionID` |
    | `Option_Text`| `VARCHAR(100)`| The text of the option                        |
    | `Is_Correct` | `TINYINT(1)` | `1` if correct, `0` if incorrect              |

    **`student_answer` table:** Records student responses to quiz questions.
    | Column          | Type         | Description                                   |
    | :-------------- | :----------- | :-------------------------------------------- |
    | `AnswerID`      | `INT`        | Primary Key, Auto-increment                   |
    | `UserID`        | `INT`        | Foreign Key referencing `user.UserID`         |
    | `QuestionID`    | `INT`        | Foreign Key referencing `question.QuestionID` |
    | `Selected_Answer`| `INT`        | ID of the option selected by the student      |
    | `Answer_Date`   | `TIMESTAMP`  | Timestamp of the answer submission            |
    | `Score`         | `INT`        | Score obtained for this specific answer (e.g., 1 for correct, 0 for incorrect)|

    **`chatbot_log` table:** Logs interactions with the AI chatbot.
    | Column           | Type         | Description                                   |
    | :--------------- | :----------- | :-------------------------------------------- |
    | `LogID`          | `INT`        | Primary Key, Auto-increment                   |
    | `UserID`         | `INT`        | Foreign Key referencing `user.UserID`         |
    | `Query`          | `TEXT`       | User's input query to the chatbot             |
    | `Response`       | `TEXT`       | Chatbot's generated response                  |
    | `Confidence_Score`| `FLOAT`      | (Optional) Confidence score of the response   |
    | `Log_Date`       | `TIMESTAMP`  | Timestamp of the interaction                  |

3.  **Configure database connection:**
    Open the `config.php` file located in the `elearning_platform/` directory.
    Ensure the database connection details within this file match your MySQL server configuration:

    ```php
    $host = 'localhost';
    $username = 'root';
    $password = ''; // Your MySQL password here
    $database = 'elearning_platform';
    ```
    -   `$host`: Your database host (typically `localhost`).
    -   `$username`: Your MySQL username (default is `root` for XAMPP/WAMP).
    -   `$password`: Your MySQL password. If you have set a password for your MySQL `root` user, enter it here. Otherwise, leave it empty (`''`).
    -   `$database`: The name of the database you created in the previous step (`elearning_platform`).

4.  **Configure OpenRouter API Key:**
    The chatbot functionality relies on the OpenRouter API. For security reasons, it's highly recommended to store your API key as an environment variable. The project uses `phpdotenv` to load these variables.

    Create a new file named `.env` in the root of your `elearning_platform` directory (the same directory as `index.php` and `config.php`). Add the following line to this `.env` file, replacing `your_openrouter_api_key_here` with your actual OpenRouter API key:

    ```
    OPENROUTER_API_KEY="your_openrouter_api_key_here"
    ```
    You can obtain an OpenRouter API key from their website after signing up.

## Usage

Once the installation and database setup are complete, you can access and use the E-Learning Platform:

1.  **Start your web server:**
    Ensure that your Apache (or equivalent) and MySQL services are running through your XAMPP/WAMP/LAMP control panel.

2.  **Access the application:**
    Open your web browser and navigate to the URL where your project is hosted. If you placed it in `htdocs` as `elearning_platform`, the URL will typically be:
    `http://localhost/elearning_platform`

3.  **Register/Login:**
    -   **For Students:** You can register a new student account through the registration page. Once registered, log in to access the student dashboard, view lecture notes, and take quizzes.
    -   **For Administrators:** The `elearning_platform.sql` file might contain a default admin user (e.g., `admin` with a pre-hashed password). Check the `user` table in your database for initial admin credentials, or register a new user and manually change their `Role` to `admin` in the database if needed.

4.  **Explore Features:**
    -   **Admin Panel:** Administrators can upload new lecture notes, create quizzes, add questions and options, and manage users.
    -   **Student Panel:** Students can browse uploaded notes, attempt quizzes, and interact with the chatbot for assistance.

## Screenshots

-   **Home Page:**
    ![Home Page Screenshot]
    <img width="1904" height="967" alt="image" src="https://github.com/user-attachments/assets/d2964e3f-995b-493d-baaa-45bc8ae9d7f1" />
    <img width="1901" height="716" alt="image" src="https://github.com/user-attachments/assets/cf1e77e4-ec09-46e4-a8b6-a827c06244ff" />
    <img width="1894" height="641" alt="image" src="https://github.com/user-attachments/assets/d0fcde8a-e054-4d79-aa9e-0d98c3bb03b1" />

-   **Student Dashboard:**
    ![Student Dashboard Screenshot]
    <img width="1917" height="969" alt="image" src="https://github.com/user-attachments/assets/b144b9f0-65ee-49ed-b258-2856a7baceca" />
    <img width="1887" height="914" alt="image" src="https://github.com/user-attachments/assets/d14d1337-5080-41e7-ab38-bf46bb6035f3" />
    <img width="1897" height="916" alt="image" src="https://github.com/user-attachments/assets/020797fe-4ad7-4ed5-8480-a9b2363e769f" />

-   **Admin Dashboard:**
    ![Admin Dashboard Screenshot]
    <img width="1892" height="966" alt="image" src="https://github.com/user-attachments/assets/4458d2ba-d647-4716-9b31-a60f5aa679bf" />
    <img width="1890" height="919" alt="image" src="https://github.com/user-attachments/assets/8374886f-33f7-4dc2-8669-4095b73cf01d" />
    <img width="1900" height="919" alt="image" src="https://github.com/user-attachments/assets/dc2a0e43-d77b-451d-84b6-6959dbd76af7" />

-   **Quiz Taking Interface:**
    ![Quiz Interface Screenshot]
    <img width="1886" height="967" alt="image" src="https://github.com/user-attachments/assets/e5ac991d-f85d-4d8e-9aee-2939d875df8a" />
    <img width="1887" height="695" alt="image" src="https://github.com/user-attachments/assets/dcd13fd3-679d-4da8-9501-9f3e728edc4e" />
    <img width="1909" height="926" alt="image" src="https://github.com/user-attachments/assets/ce27e9d1-09ef-41d0-bf86-39a9b5610e4d" />
    <img width="1879" height="913" alt="image" src="https://github.com/user-attachments/assets/f517cd4c-62eb-45de-a45d-02ce87dafcc9" />
    <img width="1880" height="760" alt="image" src="https://github.com/user-attachments/assets/8643dedb-3543-4c63-8c1f-5c9ecfc69be1" />

-   **Chatbot Interaction:**
    ![Chatbot Screenshot]
    <img width="1888" height="926" alt="image" src="https://github.com/user-attachments/assets/f8a9e5ce-24cd-4d74-be0e-dbb91da354bf" />

## Contributing

Contributions are welcome! If you'd like to contribute to this project, please follow these steps:

1.  **Fork the repository:** Click the 'Fork' button at the top right of the project's GitHub page.
2.  **Clone your forked repository:**
    ```bash
    git clone https://github.com/your-username/elearning-platform.git
    ```
3.  **Create a new branch:**
    ```bash
    git checkout -b feature/your-feature-name
    ```
    (Choose a descriptive name for your feature or bug fix.)
4.  **Make your changes:** Implement your features or bug fixes.
5.  **Commit your changes:**
    ```bash
    git commit -m 'Add a concise and descriptive commit message'
    ```
6.  **Push to the branch:**
    ```bash
    git push origin feature/your-feature-name
    ```
7.  **Open a Pull Request:** Go to the original repository on GitHub and open a new pull request from your forked repository and branch.

## License

This project is open-source and available under the [MIT License](LICENSE.md).

---

