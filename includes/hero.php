<?php
function heroSection($title, $subtitle, $image, $height = '60vh') {
  echo "
  <section class='d-flex align-items-center justify-content-center text-center text-white'
           style='height:$height; background:url($image) no-repeat center/cover;'>
    <div style=\"background:rgba(0,0,0,0.5); padding:30px; border-radius:12px;\" class=\"fade-in\">
      <h1 class='fw-bold'>$title</h1>
      <p>$subtitle</p>
    </div>
  </section>";
}
?>
