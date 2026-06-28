USE uitm_step;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE sessions;
TRUNCATE TABLE gig_tags;
TRUNCATE TABLE reviews;
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

-- Insert Gigs (27 Gigs)
INSERT INTO gigs (seller_id, title, description, price, category, status) VALUES
(5, 'Web Development Fixes', 'I will fix your HTML/CSS bugs.', 50.00, 'Tech', 'active'),
(8, 'Logo Design for FYP', 'Minimalist logo design for your final year project.', 30.00, 'Creative', 'active'),
(5, 'Calculus Tutoring', '1 hour 1-on-1 calculus tutoring.', 20.00, 'Education', 'active'),
(5, 'Data Entry', 'Fast data entry using Excel. 1000 rows.', 15.00, 'Admin', 'active'),
(9, 'Resume Review', 'I will review and edit your resume.', 10.00, 'Career', 'active'),
(5, 'Video Editing for Assignment', 'I will edit your 5-min assignment video.', 40.00, 'Creative', 'active'),
(8, 'PHP Script Creation', 'Small PHP script for your project.', 60.00, 'Tech', 'active'),
(9, 'Flyer Design for Event', 'Eye-catching flyer for UiTM events.', 25.00, 'Creative', 'active'),
(5, 'Physics Tutoring', '1 hour physics tutoring.', 20.00, 'Education', 'active'),
(8, 'Python Web Scraper', 'Scrape small websites for data.', 45.00, 'Tech', 'active'),
(9, 'UI/UX Prototype in Figma', '3 screens mockup.', 35.00, 'Creative', 'active'),
(7, 'Proofreading Essay', 'Up to 2000 words.', 15.00, 'Writing', 'active'),
(8, 'Java Debugging', 'Fix Java assignment bugs.', 25.00, 'Tech', 'active'),
(9, 'Custom Illustration', 'Small digital illustration.', 20.00, 'Creative', 'active'),
(7, 'Translate English to Malay', 'Up to 1500 words.', 18.00, 'Writing', 'active'),
(6, 'Mobile App UI Design', '5 screens high-fidelity mockup in Figma.', 55.00, 'Creative', 'active'),
(7, 'Presentation Slides Design', 'Modern and professional slides for your assessment.', 20.00, 'Creative', 'active'),
(8, 'SQL Database Optimization', 'Improve your project database performance.', 40.00, 'Tech', 'active'),
(9, 'Poster Design', 'Creative posters for club events or coursework.', 25.00, 'Creative', 'active'),
(5, 'Malay Literature Tutoring', 'Deep dive into literature analysis.', 20.00, 'Education', 'active'),
(6, 'Article Writing', '1000 words article for your blog or assignment.', 30.00, 'Writing', 'active'),
(7, 'Sound Mixing', 'Quality audio mixing for your video project.', 35.00, 'Creative', 'active'),
(8, 'C++ Assignment Help', 'Algorithm and data structure debugging.', 30.00, 'Tech', 'active'),
(9, 'Business Plan Template', 'Ready-to-use template for Entreprenurship projects.', 15.00, 'Writing', 'active'),
(5, 'Lab Report Proofreading', 'Scientific report checking for accuracy.', 20.00, 'Writing', 'active'),
(6, 'Excel Dashboard Creation', 'Visualize your data with charts and pivots.', 50.00, 'Tech', 'active'),
(7, 'Infographic Design', 'Turn your data into beautiful visuals.', 30.00, 'Creative', 'active');

-- Assign Tags to Gigs (Junction Table)
INSERT INTO gig_tags (gig_id, tag_id) VALUES
(1, 1), (2, 2), (3, 4), (4, 3), (5, 3), (6, 5), (7, 1), (8, 2),
(9, 4), (10, 1), (11, 2), (12, 3), (13, 1), (14, 2), (15, 3),
(16, 2), (17, 2), (18, 1), (19, 2), (20, 4), (21, 3), (22, 5), (23, 1), (24, 3), (25, 3), (26, 1), (27, 2);

-- Insert completed orders for seeding reviews (each gig gets at least one review)
INSERT INTO orders (buyer_id, gig_id, status) VALUES
(6, 1, 'complete'),
(7, 1, 'complete'),
(8, 2, 'complete'),
(9, 2, 'complete'),
(5, 3, 'complete'),
(6, 4, 'complete'),
(7, 4, 'complete'),
(7, 5, 'complete'),
(8, 6, 'complete'),
(9, 7, 'complete'),
(5, 8, 'complete'),
(6, 9, 'complete'),
(7, 10, 'complete'),
(8, 11, 'complete'),
(9, 12, 'complete'),
(5, 13, 'complete'),
(6, 14, 'complete'),
(7, 15, 'complete'),
(8, 16, 'complete'),
(9, 17, 'complete'),
(5, 18, 'complete'),
(6, 19, 'complete'),
(7, 20, 'complete'),
(8, 21, 'complete'),
(9, 22, 'complete'),
(5, 23, 'complete'),
(6, 24, 'complete'),
(7, 25, 'complete'),
(8, 26, 'complete'),
(9, 27, 'complete');

-- Insert Reviews corresponding to completed orders
INSERT INTO reviews (order_id, gig_id, buyer_id, seller_id, rating, review_text) VALUES
(1, 1, 6, 5, 5, 'Excellent service, fixed my HTML/CSS bug in minutes!'),
(2, 1, 7, 5, 4, 'Good work, very helpful response.'),
(3, 2, 8, 8, 5, 'Super cool logo design, matches our FYP theme perfectly.'),
(4, 2, 9, 8, 5, 'Highly creative! The custom illustration is fantastic.'),
(5, 3, 5, 5, 5, 'Clear explanation on calculus topics, helped with assignment!'),
(6, 4, 6, 5, 4, 'Accurate data entry, clean work.'),
(7, 4, 7, 5, 5, 'Super fast delivery of my Excel data entries.'),
(8, 5, 7, 9, 5, 'Great feedback on my resume. Got selected for interview!'),
(9, 6, 8, 5, 5, 'Excellent editing, the transitions are very clean and professional.'),
(10, 7, 9, 8, 5, 'Very robust PHP script, solved our database retrieval issues.'),
(11, 8, 5, 9, 5, 'Amazing flyer design! Standard is very high.'),
(12, 9, 6, 5, 5, 'Helped me understand physics concepts so clearly.'),
(13, 10, 7, 8, 4, 'Simple python scraper, runs perfectly.'),
(14, 11, 8, 9, 5, 'The Figma prototype is stunning and interactive.'),
(15, 12, 9, 7, 5, 'Perfect proofreading, corrected all grammatical errors.'),
(16, 13, 5, 8, 5, 'Helped debug my Java project, very clear explanation.'),
(17, 14, 6, 9, 5, 'Fabulous illustration, very creative style.'),
(18, 15, 7, 7, 4, 'Very accurate translation to Malay. Fast delivery.'),
(19, 16, 8, 6, 5, 'Highly detailed UI design, very professional Figma layout.'),
(20, 17, 9, 7, 5, 'Slide design is clean, sleek, and matches my topic.'),
(21, 18, 5, 8, 5, 'Optimized query runs 10x faster now, great work!'),
(22, 19, 6, 9, 5, 'Beautiful club event posters! Highly satisfied.'),
(23, 20, 7, 5, 5, 'The literature analysis helped me secure an A.'),
(24, 21, 8, 6, 5, 'Well-written article, zero plagiarism detected.'),
(25, 22, 9, 7, 4, 'Sound is balanced and clean. Thanks!'),
(26, 23, 5, 8, 5, 'Great helper with C++ assignments, very clear logic.'),
(27, 24, 6, 9, 5, 'Template is easy to use and very comprehensive.'),
(28, 25, 7, 5, 5, 'Lab report check was meticulous and helpful.'),
(29, 26, 8, 6, 5, 'The Excel dashboard is fully interactive, beautiful colors.'),
(30, 27, 9, 7, 5, 'Amazing infographic design, made complex data readable.');
