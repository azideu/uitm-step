<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Feedback & Support | UiTM STEP</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

/* ================= ORIGINAL DARK MODE ================= */
body{
    background: radial-gradient(circle at top, #1a0f3a, #070b1a 60%);
    color:white;
}

/* ================= LIGHT MODE (ONLY ADDITION) ================= */
body.light{
    background:#f4f6fb;
    color:#111;
}

/* NAVBAR */
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 40px;
    background:#3b0066;
}

body.light .navbar{
    background:#ffffff;
    border-bottom:1px solid #ddd;
}

.logo img{
    height:40px;
}

.nav-right{
    display:flex;
    align-items:center;
    gap:20px;
}

.nav-right a{
    text-decoration:none;
    color:white;
    font-size:14px;
}

body.light .nav-right a{
    color:#111;
}

.register-btn{
    background:#ffd000;
    color:black;
    padding:8px 18px;
    border-radius:10px;
    font-weight:500;
}

/* MAIN */
.container{
    width:80%;
    margin:40px auto;
    background:rgba(255,255,255,0.05);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:20px;
    padding:40px;
    backdrop-filter: blur(10px);
}

body.light .container{
    background:#ffffff;
    border:1px solid #ddd;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

/* HEADER */
.header{
    text-align:center;
    margin-bottom:30px;
}

.header h1{
    color:#d7b3ff;
    font-size:28px;
}

body.light .header h1{
    color:#4b0082;
}

.header p{
    color:#aaa;
    font-size:14px;
}

body.light .header p{
    color:#555;
}

/* FORM */
.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:20px;
}

label{
    font-size:13px;
    color:#ccc;
}

body.light label{
    color:#333;
}

input, select, textarea{
    width:100%;
    padding:12px;
    margin-top:8px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.1);
    background:#0f1426;
    color:white;
}

body.light input,
body.light select,
body.light textarea{
    background:#fff;
    color:#111;
    border:1px solid #ccc;
}

textarea{
    grid-column:span 2;
    height:120px;
    resize:none;
}

button{
    grid-column:span 2;
    padding:14px;
    border:none;
    border-radius:12px;
    background:linear-gradient(90deg,#6a00ff,#a855f7);
    color:white;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    transform:scale(1.02);
}

/* SUPPORT BOX */
.support-box{
    margin-top:25px;
    padding:18px;
    border-radius:12px;
    background:rgba(255,255,255,0.05);
    border:1px solid rgba(255,255,255,0.1);
    text-align:center;
    font-size:14px;
    color:#ccc;
}

body.light .support-box{
    background:#f9fafc;
    border:1px solid #ddd;
    color:#444;
}

.email-btn{
    display:inline-block;
    padding:12px 18px;
    background:linear-gradient(90deg,#6a00ff,#a855f7);
    color:white;
    border-radius:12px;
    text-decoration:none;
    font-weight:500;
    margin-top:10px;
    transition:0.2s;
}

.email-btn:hover{
    transform:scale(1.05);
}

</style>
</head>

<body>


<!-- MAIN -->
<div class="container">

    <div class="header">
        <div class="title-row">
            <h1>We Value Your Feedback</h1>
        </div>
        <p>Your feedback helps us improve and deliver better services for UiTM STEP.</p>
    </div>

    <form action="insert_feedback.php" method="POST">

        <div class="form-grid">

            <div>
                <label>Name</label>
                <input type="text" name="name" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div>
                <label>Phone</label>
                <input type="text" name="phone" required>
            </div>

            <div> <label>Campus</label> 
            <select name="campus" required> 
                <option value="">-- Select --</option> 
                <optgroup label="Selangor"> 
                    <option>Shah Alam</option> 
                    <option>Puncak Alam</option> 
                    <option>Puncak Perdana</option> 
                    <option>Dengkil</option> 
                    <option>Sungai Buloh</option> 
                </optgroup> 
                <optgroup label="Melaka"> 
                    <option>Alor Gajah</option> 
                    <option>Jasin</option> 
                    <option>Bandaraya Melaka</option> 
                </optgroup> 
                <optgroup label="Johor"> 
                    <option>Segamat</option>
                    <option>Pasir Gudang</option> 
                </optgroup> 
                <optgroup label="Perak"> 
                    <option>Seri Iskandar</option> 
                    <option>Tapah</option> 
                </optgroup> 
                <optgroup label="Kedah"> 
                    <option>Sungai Petani</option> 
                </optgroup> 
                <optgroup label="Pulau Pinang"> 
                    <option>Permatang Pauh</option> 
                    <option>Bertam</option> 
                </optgroup> 
                <optgroup label="Perlis"> 
                    <option>Arau</option> 
                </optgroup> 
                    <optgroup label="Kelantan"> 
                        <option>Kota Bharu</option> 
                        <option>Machang</option> 
                    </optgroup> 
                    <optgroup label="Terengganu"> 
                        <option>Kuala Terengganu</option> 
                        <option>Dungun</option> 
                        <option>Bukit Besi</option> 
                    </optgroup> 
                    <optgroup label="Pahang"> 
                        <option>Jengka</option> 
                        <option>Raub</option> 
                    </optgroup> 
                    <optgroup label="Sabah"> 
                        <option>Kota Kinabalu</option> 
                        <option>Tawau</option> 
                    </optgroup> 
                    <optgroup label="Sarawak"> 
                        <option>Mukah</option> 
                        <option>Samarahan</option>
                         <option>Samarahan 2</option> 
                        </optgroup> 
                        <optgroup label="Negeri Sembilan">
                             <option>Rembau</option> 
                             <option>Seremban</option> 
                             <option>Kuala Pilah</option> 
                            </optgroup> 
                        </select>
            </div>

            <div>
                <label>Nature of Feedback</label>
                <select name="nature" required>
                    <option value="">-- Select Nature --</option>
                    <option>Complaint</option>
                    <option>Suggestion</option>
                    <option>Compliment</option>
                </select>
            </div>

            <textarea name="message" maxlength="500" required></textarea>

            <button type="submit">Submit</button>

        </div>
    </form>

    <div class="support-box">
        <b>Support Information</b><br><br>
        For any technical issues, inquiries, or assistance regarding the UiTM STEP system,<br> please contact us via email.<br><br>
        📧 uitmstep@gmail.com<br><br>

        <a href="mailto:uitmstep@gmail.com" class="email-btn">
        Drop Email
    </a>
    <br><br>
        Response time: Within 1–3 working days
    </div>

</div>

</body>
</html>