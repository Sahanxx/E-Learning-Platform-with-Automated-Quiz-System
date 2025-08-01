-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2025 at 11:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elearning_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_log`
--

CREATE TABLE `chatbot_log` (
  `LogID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Query` text NOT NULL,
  `Response` text NOT NULL,
  `Confidence_Score` float DEFAULT NULL,
  `Log_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_log`
--

INSERT INTO `chatbot_log` (`LogID`, `UserID`, `Query`, `Response`, `Confidence_Score`, `Log_Date`) VALUES
(46, 11, 'what is php', '**PHP** (recursive acronym for **PHP: Hypertext Preprocessor**) is a popular open-source server-side scripting language widely used for web development. It is especially suited for creating dynamic and interactive web pages. Here are some key points about PHP:\n\n### 1. **Purpose**\n   - Used to develop **dynamic websites** and **web applications**.\n   - Allows you to create content that interacts with databases, processes forms, generates dynamic page content, and more.\n\n### 2. **Key Features**\n   - **Server-Side**: PHP code is executed on the server, and the result is sent to the client as plain HTML.\n   - **Open Source**: Free to use and has a large community for support.\n   - **Cross-Platform**: Runs on various platforms like Windows, Linux, macOS, etc.\n   - **Database Integration**: Works seamlessly with databases like MySQL, PostgreSQL, Oracle, etc.\n   - **Embeddable**: PHP code can be embedded directly into HTML.\n\n### 3. **Syntax**\n   - PHP code is enclosed within `<?php ?>` tags.\n   - Example:\n     ```php\n     <?php\n     echo \"Hello, World!\";\n     ?>\n     ```\n\n### 4. **Common Uses**\n   - Building dynamic websites.\n   - Handling forms and user input.\n   - Managing cookies and sessions.\n   - Interacting with databases (e.g., MySQL).\n   - Creating REST APIs.\n\n### 5. **Why Learn PHP?**\n   - Easy to learn for beginners.\n   - Widely used in popular CMS platforms like **WordPress**, **Joomla**, and **Drupal**.\n   - High demand in the job market for web developers.\n\nIf you\'re interested in learning PHP, start with basic syntax, then move on to working with forms, databases, and frameworks like Laravel. Let me know if you\'d like study resources or examples! 😊', NULL, '2025-06-10 07:42:36'),
(47, 11, 'how do i prepare for quizzes?', 'Quizzes help you test your knowledge! Visit the \'Take Quiz\' section on your dashboard to start one.', NULL, '2025-06-10 08:05:28'),
(48, 11, 'show me study tips', 'Certainly! Here are some effective study tips to help you maximize your learning and retention:\n\n### **1. Create a Study Schedule**\n   - **Set specific times** for studying each day or week.\n   - **Break sessions into chunks** (e.g., 25-50 minutes) with short breaks in between (Pomodoro Technique).\n   - **Prioritize tasks** based on deadlines and difficulty.\n\n### **2. Find Your Ideal Study Environment**\n   - Choose a **quiet, distraction-free space** with good lighting.\n   - Keep your study area **organized** and free of clutter.\n   - Experiment with **background music or white noise** if it helps you focus.\n\n### **3. Use Active Learning Techniques**\n   - **Summarize** material in your own words.\n   - **Teach the concept** to someone else or pretend to teach it.\n   - **Create flashcards** for key terms and concepts.\n   - **Apply knowledge** through practice problems or real-world examples.\n\n### **4. Take Effective Notes**\n   - Use **bullet points, headings, and highlighting** to organize information.\n   - Try the **Cornell Note-Taking System** for better structure.\n   - Review and **revise notes** after class to reinforce learning.\n\n### **5. Stay Engaged During Lectures**\n   - **Ask questions** if you don’t understand something.\n   - **Participate in discussions** to deepen your understanding.\n   - **Record lectures** (if allowed) to revisit later.\n\n### **6. Utilize Study Tools and Resources**\n   - Use **apps** like Quizlet, Anki, or Notion for organizing and reviewing material.\n   - Watch **educational videos** or tutorials for visual explanations.\n   - Access **past papers or practice quizzes** to test your knowledge.\n\n### **7. Practice Retrieval and Spaced Repetition**\n   - **Test yourself** regularly to reinforce memory.\n   - Use **spaced repetition** to review material at increasing intervals.\n   - Focus on **weaker areas** to improve understanding.\n\n### **8. Stay Healthy and Balanced**\n   - Get **enough sleep** (7-9 hours) to improve focus and memory.\n   - Eat **nutritious meals** and stay hydrated.\n   - Exercise regularly to reduce stress and boost brain function.\n\n### **9. Form or Join Study Groups**\n   - Collaborate with peers to **discuss and clarify concepts**.\n   - Teach and learn from others to reinforce understanding.\n   - Stay accountable to your study goals.\n\n### **10. Reward Yourself**\n   - Set **small rewards** for completing tasks (e.g., a break, snack, or leisure activity).\n   - Celebrate **milestones** to stay motivated.\n\n### **11. Stay Positive and Manage Stress**\n   - Practice mindfulness or meditation to reduce anxiety.\n   - Avoid **procrastination** by starting tasks early.\n   - Remind yourself of your **goals** and why your studies matter.\n\nLet me know if you’d like more tailored advice! 😊', NULL, '2025-06-10 11:54:09'),
(49, 11, 'hi', 'Hello! 👋 How can I assist you today? Are you looking for help with a quiz, lecture notes, or general study tips? Let me know! 😊', NULL, '2025-06-11 06:08:54'),
(50, 11, 'hi', 'Hello! 👋 How can I assist you today? Are you looking for help with quizzes, lecture notes, or general study tips? Let me know! 😊', NULL, '2025-06-11 06:08:58'),
(51, 11, 'hi', 'Hello! 😊 How can I assist you today? Are you looking for help with quiz preparation, lecture notes, or general study tips? Let me know! 📚✨', NULL, '2025-06-11 09:43:25'),
(52, 11, 'hi', 'Hello! 👋 How can I assist you today? Are you looking for help with quizzes, lecture notes, or general study tips? Let me know! 😊', NULL, '2025-06-11 09:43:29');

-- --------------------------------------------------------

--
-- Table structure for table `lecture_note`
--

CREATE TABLE `lecture_note` (
  `NoteID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `File_Path` varchar(255) NOT NULL,
  `Upload_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecture_note`
--

INSERT INTO `lecture_note` (`NoteID`, `UserID`, `Title`, `File_Path`, `Upload_Date`) VALUES
(49, 1, 'network', 'uploads/6849720045195_HNDIT 1022 Week 01 Theory.pdf', '2025-06-11 12:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `option`
--

CREATE TABLE `option` (
  `OptionID` int(11) NOT NULL,
  `QuestionID` int(11) NOT NULL,
  `Option_Text` varchar(100) NOT NULL,
  `Is_Correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `option`
--

INSERT INTO `option` (`OptionID`, `QuestionID`, `Option_Text`, `Is_Correct`) VALUES
(1005, 252, 'Advanced programming in Python', 0),
(1006, 252, 'Fundamentals of web design using HTML, CSS, and JavaScript', 1),
(1007, 252, 'Database management systems', 0),
(1008, 252, 'Network security protocols', 0),
(1009, 253, 'Bill Gates', 0),
(1010, 253, 'Steve Jobs', 0),
(1011, 253, 'Tim Berners-Lee', 1),
(1012, 253, 'Mark Zuckerberg', 0),
(1013, 254, 'HyperText Transfer Protocol', 1),
(1014, 254, 'HyperText Transmission Process', 0),
(1015, 254, 'HyperText Technical Protocol', 0),
(1016, 254, 'HyperText Transfer Process', 0),
(1017, 255, 'Shared Hosting', 0),
(1018, 255, 'Cloud Hosting', 0),
(1019, 255, 'Dedicated Hosting', 0),
(1020, 255, 'Firewall Hosting', 1),
(1021, 256, 'To encrypt data on the web', 0),
(1022, 256, 'To transfer files between computers over a network', 1),
(1023, 256, 'To create graphical user interfaces', 0),
(1024, 256, 'To manage database queries', 0),
(1025, 257, 'Internet Engineering Task Force (IETF)', 0),
(1026, 257, 'World Wide Web Consortium (W3C)', 1),
(1027, 257, 'International Organization for Standardization (ISO)', 0),
(1028, 257, 'Web Hypertext Application Technology Working Group (WHATWG)', 0),
(1029, 258, 'It ensures faster internet speeds', 0),
(1030, 258, 'It allows a website to function properly across different browsers', 1),
(1031, 258, 'It improves search engine optimization', 0),
(1032, 258, 'It reduces the need for web hosting', 0),
(1033, 259, 'Checking for the cheapest plan', 0),
(1034, 259, 'Understanding your website\'s needs', 1),
(1035, 259, 'Choosing a provider with the most servers', 0),
(1036, 259, 'Ignoring the server\'s uptime guarantees', 0),
(1037, 260, 'To define the structure of a webpage', 0),
(1038, 260, 'To add interactivity to a webpage', 0),
(1039, 260, 'To style and format the layout of a webpage', 1),
(1040, 260, 'To manage database connections', 0),
(1041, 261, 'To secure websites from cyber threats', 0),
(1042, 261, 'To create and upload content on the internet', 1),
(1043, 261, 'To design interactive animations', 0),
(1044, 261, 'To manage network servers', 0);

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `QuestionID` int(11) NOT NULL,
  `QuizID` int(11) NOT NULL,
  `Question_Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`QuestionID`, `QuizID`, `Question_Text`) VALUES
(252, 51, 'What is the primary focus of the HNDIT1022 Web Design module?'),
(253, 51, 'Who invented the World Wide Web (WWW)?'),
(254, 51, 'What does HTTP stand for in the context of web design?'),
(255, 51, 'Which of the following is NOT a type of web hosting?'),
(256, 51, 'What is the purpose of FTP in web design?'),
(257, 51, 'Which organization is responsible for maintaining global standards for HTML?'),
(258, 51, 'What is the significance of browser compatibility in web design?'),
(259, 51, 'What is the first step in selecting a web hosting provider?'),
(260, 51, 'What is the role of CSS in web design?'),
(261, 51, 'What is the primary purpose of web publishing?');

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `QuizID` int(11) NOT NULL,
  `NoteID` int(11) NOT NULL,
  `Quiz_Title` varchar(100) NOT NULL,
  `Creation_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`QuizID`, `NoteID`, `Quiz_Title`, `Creation_Date`) VALUES
(51, 49, 'network', '2025-06-11 12:10:10');

-- --------------------------------------------------------

--
-- Table structure for table `student_answer`
--

CREATE TABLE `student_answer` (
  `AnswerID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `QuestionID` int(11) NOT NULL,
  `Selected_Answer` int(11) DEFAULT NULL,
  `Answer_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('admin','student') NOT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Username`, `Email`, `Password`, `Role`, `Created_At`) VALUES
(1, 'admin', 'master@gmail.com', '$2y$10$NKdFNRPXyQf3ySXxJwMmB./31iFfhyMtSSy03SWmEbzHJEwaCP4H.', 'admin', '2025-03-26 05:31:42'),
(11, 'Nuwan', 'nuwanthathsara111@gmail.com', '$2y$10$PIkDnDkqUbtn9A2AKi/0LedcYctPuoAKDN6I8aWtr3Ctc4EXGy40C', 'student', '2025-06-10 07:34:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chatbot_log`
--
ALTER TABLE `chatbot_log`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `idx_userid` (`UserID`);

--
-- Indexes for table `lecture_note`
--
ALTER TABLE `lecture_note`
  ADD PRIMARY KEY (`NoteID`),
  ADD KEY `idx_userid` (`UserID`);

--
-- Indexes for table `option`
--
ALTER TABLE `option`
  ADD PRIMARY KEY (`OptionID`),
  ADD KEY `idx_questionid` (`QuestionID`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`QuestionID`),
  ADD KEY `idx_quizid` (`QuizID`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`QuizID`),
  ADD KEY `idx_noteid` (`NoteID`);

--
-- Indexes for table `student_answer`
--
ALTER TABLE `student_answer`
  ADD PRIMARY KEY (`AnswerID`),
  ADD KEY `Selected_Answer` (`Selected_Answer`),
  ADD KEY `idx_userid` (`UserID`),
  ADD KEY `idx_questionid` (`QuestionID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_username` (`Username`),
  ADD KEY `idx_email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chatbot_log`
--
ALTER TABLE `chatbot_log`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `lecture_note`
--
ALTER TABLE `lecture_note`
  MODIFY `NoteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `option`
--
ALTER TABLE `option`
  MODIFY `OptionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1045;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `QuestionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `QuizID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `student_answer`
--
ALTER TABLE `student_answer`
  MODIFY `AnswerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chatbot_log`
--
ALTER TABLE `chatbot_log`
  ADD CONSTRAINT `chatbot_log_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `lecture_note`
--
ALTER TABLE `lecture_note`
  ADD CONSTRAINT `lecture_note_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `option`
--
ALTER TABLE `option`
  ADD CONSTRAINT `option_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `question` (`QuestionID`) ON DELETE CASCADE;

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`QuizID`) REFERENCES `quiz` (`QuizID`) ON DELETE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`NoteID`) REFERENCES `lecture_note` (`NoteID`) ON DELETE CASCADE;

--
-- Constraints for table `student_answer`
--
ALTER TABLE `student_answer`
  ADD CONSTRAINT `student_answer_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answer_ibfk_2` FOREIGN KEY (`QuestionID`) REFERENCES `question` (`QuestionID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
