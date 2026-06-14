<!DOCTYPE html>
<html>
<head>
<title>Thank You</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/style.css">

<style>
body{
    margin:0;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:'Poppins', sans-serif;
}

/* button custom */
.btn{
    padding:12px 20px;
    border-radius:10px;
    text-decoration:none;
    background:#FFD700;
    color:#330066;
    font-weight:600;
    display:inline-block;
    margin-top:20px;
    transition:0.2s;
}

.btn:hover{
    transform:scale(1.05);
}

/* card spacing */
.card{
    text-align:center;
    width:420px;
}
</style>
</head>

<body class="bg-flat-gradient">

<!-- noise overlay -->
<div class="bg-noise" style="position:absolute; inset:0;"></div>

<div style="display:flex; justify-content:center; align-items:center; width:100%; height:100%; position:relative; z-index:2;">

    <!-- glass panel style from your CSS -->
    <div class="card glass-panel animate-on-scroll visible" style="padding:40px; border-radius:20px;">

        <!-- icon -->
        <div style="color:#F5DADF; font-size:60px;">݁ ˖Ი𐑼ֶָ֢</div>

        <!-- title -->
        <h1 style="color:#330066; margin:0;">We hear you</h1>

        <!-- message -->
        <p style="color:#555; line-height:1.6;">
            Thank you for your feedback!<br><br>

            We truly appreciate you taking the time to share your thoughts with us. 
            Your feedback has been successfully submitted and will be reviewed by our team.<br><br>

            It plays an important role in helping us improve and serve you better.
        </p>

        <!-- response time -->
        <p style="color:#777; font-size:13px; margin-top:10px;">
            Response time: within 1–3 working days
        </p>

        <!-- button -->
        <a href="feedback.php" class="btn">Back to Form</a>

    </div>

</div>

</body>
</html>