USE uitm_step;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE gig_tags;
TRUNCATE TABLE orders;
TRUNCATE TABLE messages;
TRUNCATE TABLE gigs;
TRUNCATE TABLE tags;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert Admins
INSERT INTO users (student_id, name, email, password, campus, role) VALUES
('2024801742', 'Addin Zidane', '2024801742@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kuala Terengganu', 'admin'),
('2024236824', 'Muhammad Zahin Shah', '2024236824@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kuala Terengganu', 'admin'),
('2024244864', 'Danish Daniel', '2024244864@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kuala Terengganu', 'admin'),
('2024419672', 'Awin Amani', '2024419672@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kuala Terengganu', 'admin');

-- Insert Students (Password is 'password')
INSERT INTO users (student_id, name, email, password, campus, role) VALUES
('2024663124', 'Ahmad Zaidan', '2024663124@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shah Alam', 'student'),
('2024237928', 'Amanda Saffieya', '2024237928@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Puncak Alam', 'student'),
('2024284386', 'Ainun Nadiah', '2024284386@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jengka', 'student'),
('2024288856', 'Adam Khairi', '2024288856@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shah Alam', 'student'),
('2024285674', 'Muhammad Ridhuan', '2024285674@student.uitm.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Seri Iskandar', 'student');

-- Insert Tags
INSERT INTO tags (name) VALUES ('Programming'), ('Design'), ('Writing'), ('Tutor'), ('Video');

-- Insert Gigs (15 Gigs)
INSERT INTO gigs (seller_id, title, description, price, category, status) VALUES
(3, 'Web Development Fixes', 'I will fix your HTML/CSS bugs.', 50.00, 'Tech', 'active'),
(4, 'Logo Design for FYP', 'Minimalist logo design for your final year project.', 30.00, 'Creative', 'active'),
(5, 'Calculus Tutoring', '1 hour 1-on-1 calculus tutoring.', 20.00, 'Education', 'active'),
(3, 'Data Entry', 'Fast data entry using Excel. 1000 rows.', 15.00, 'Admin', 'active'),
(4, 'Resume Review', 'I will review and edit your resume.', 10.00, 'Career', 'active'),
(5, 'Video Editing for Assignment', 'I will edit your 5-min assignment video.', 40.00, 'Creative', 'active'),
(3, 'PHP Script Creation', 'Small PHP script for your project.', 60.00, 'Tech', 'active'),
(4, 'Flyer Design for Event', 'Eye-catching flyer for UiTM events.', 25.00, 'Creative', 'active'),
(5, 'Physics Tutoring', '1 hour physics tutoring.', 20.00, 'Education', 'active'),
(3, 'Python Web Scraper', 'Scrape small websites for data.', 45.00, 'Tech', 'active'),
(4, 'UI/UX Prototype in Figma', '3 screens mockup.', 35.00, 'Creative', 'active'),
(5, 'Proofreading Essay', 'Up to 2000 words.', 15.00, 'Writing', 'active'),
(3, 'Java Debugging', 'Fix Java assignment bugs.', 25.00, 'Tech', 'active'),
(4, 'Custom Illustration', 'Small digital illustration.', 20.00, 'Creative', 'active'),
(5, 'Translate English to Malay', 'Up to 1500 words.', 18.00, 'Writing', 'active'),
(6, 'Mobile App UI Design', '5 screens high-fidelity mockup in Figma.', 55.00, 'Creative', 'active'),
(7, 'Presentation Slides Design', 'Modern and professional slides for your assessment.', 20.00, 'Creative', 'active'),
(3, 'SQL Database Optimization', 'Improve your project database performance.', 40.00, 'Tech', 'active'),
(4, 'Poster Design', 'Creative posters for club events or coursework.', 25.00, 'Creative', 'active'),
(5, 'Malay Literature Tutoring', 'Deep dive into KOMSAS and literature analysis.', 20.00, 'Education', 'active'),
(6, 'Article Writing', '1000 words article for your blog or assignment.', 30.00, 'Writing', 'active'),
(7, 'Sound Mixing', 'Quality audio mixing for your video project.', 35.00, 'Creative', 'active'),
(3, 'C++ Assignment Help', 'Algorithm and data structure debugging.', 30.00, 'Tech', 'active'),
(4, 'Business Plan Template', 'Ready-to-use template for Entreprenurship projects.', 15.00, 'Writing', 'active'),
(5, 'Lab Report Proofreading', 'Scientific report checking for accuracy.', 20.00, 'Writing', 'active'),
(6, 'Excel Dashboard Creation', 'Visualize your data with charts and pivots.', 50.00, 'Tech', 'active'),
(7, 'Infographic Design', 'Turn your data into beautiful visuals.', 30.00, 'Creative', 'active');

-- Assign Tags to Gigs (Junction Table)
INSERT INTO gig_tags (gig_id, tag_id) VALUES
(1, 1), (2, 2), (3, 4), (4, 3), (5, 3), (6, 5), (7, 1), (8, 2),
(9, 4), (10, 1), (11, 2), (12, 3), (13, 1), (14, 2), (15, 3),
(16, 2), (17, 2), (18, 1), (19, 2), (20, 4), (21, 3), (22, 5), (23, 1), (24, 3), (25, 3), (26, 1), (27, 2);
