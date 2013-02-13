<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>dylansserver.com/notes/rss</title>
    <link>https://dylansserver.com/notes</link>
    <description>dylansserver.com/notes/rss</description>
    <atom:link href="https://dylansserver.com/notes/rss" rel="self" type="application/rss+xml" />
    <?php
      foreach ($this->items as $item) {
        echo "<item>";
        echo "  <title>" . $item['title'] . "</title>";
        echo "  <link>" . $item['url'] . "</link>";
        echo "  <guid>" . $item['url'] . "</guid>";
        echo "  <description>" . $item['description'] . "</description>";
        echo "</item>";
      }
    ?>
  </channel>
</rss>
