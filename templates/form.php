<form method='post' action='simplefeed/actions/save.php'>
<input type='hidden' name='slug' value='<?php echo $post['slug']; ?>'>
Titel: <input name='title' value='<?php echo $post['title']; ?>'><br>
Datum: <input name='date' value='<?php echo $post['date']; ?>'><br>
Kurztext: <input name='short' value='<?php echo $post['short']; ?>'><br>
Inhalt: <textarea name='content'><?php echo $post['content']; ?></textarea><br>
Bild-URL: <input name='image' value='<?php echo $post['image']; ?>'><br>
Tags (kommagetrennt): <input name='tags' value='<?php echo implode(',', $post['tags']); ?>'><br>
Autor: <input name='author' value='<?php echo $post['author']; ?>'><br>
<input type='submit' value='Speichern'>
</form>