<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SALIKSIC</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --primary:#0d6efd;
      --primary-hover:#0b5ed7;
      --card-bg:#ffffff;
      --text:#1f2937;
      --muted:#6b7280;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji", "Segoe UI Symbol";
      color:var(--text);
      background: linear-gradient(180deg,#f7fafc 0%, #eef2f7 100%);
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
    }
    .shell{
      width:min(100%, 980px);
    }
    header{
      background:var(--card-bg);
      border-radius:16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.06);
      padding:28px 24px;
      text-align:center;
    }
    header img{
      width:100%;
      max-width:720px;
      height:auto;
      display:block;
      margin:0 auto 12px auto;
    }
    .fineprint{
      font-size:.9rem;
      color:var(--muted);
      margin-top:6px;
    }
    .section-label{
      margin-top:28px;
      font-size:1.2rem;
      font-weight:600;
      color:#374151;
    }
    nav{
      margin:18px 0 0 0;
      display:flex;
      gap:12px;
      justify-content:center;
      flex-wrap:wrap;
    }
    .btn{
      --bg: var(--primary);
      --bg-hover: var(--primary-hover);
      --color:#fff;
      display:inline-flex;
      align-items:center;
      gap:.5rem;
      text-decoration:none;
      background:var(--bg);
      color:var(--color);
      padding:12px 18px;
      border-radius:10px;
      border:1px solid transparent;
      transition: filter .15s ease, transform .02s ease;
      font-weight:600;
    }
    .btn:focus{outline:2px solid rgba(13,110,253,.35); outline-offset:2px}
    .btn:hover{filter:brightness(.95)}
    footer{
      text-align:center;
      font-size:.85rem;
      color:#374151;
      margin-top:18px;
    }
    footer a{color:inherit}
    footer a:hover{text-decoration:none}
  </style>
</head>
<body>

  <div class="shell">
    <header>
      <!-- Put saliksic-header.png in the same folder as index.php -->
      <img src="saliksic-header.png" alt="SALIKSIC — Systems and Learning for Inclusive Knowledge and Services in Innovation and Care">
      <div class="fineprint">by JK to MG</div>

      <div class="section-label">Critical Appraisal</div>
      <div class="item">Therapy</div>
      <nav>
        <a class="btn" href="article_form.php">➕ New Study</a>
        <a class="btn" href="studies_list.php">📑 View Studies</a>
      </nav>
    </header>

    <footer>
      <p>&copy; <?php echo date("Y"); ?> SALIKSIC Project. Some Rights Reserved.</p>
      <p>
        Licensed under
        <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/" target="_blank" rel="noopener">
          Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0)
        </a>.
      </p>
    </footer>
  </div>

</body>
</html>
