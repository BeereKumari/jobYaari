-- Fix existing data corrupted by faulty sanitize() function that was
-- double-encoding HTML entities on every save.
-- Run this ONCE after applying the code fix.

UPDATE blogs SET
  title = html_entity_decode(title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
  slug = html_entity_decode(slug, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
  short_description = html_entity_decode(short_description, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
  meta_keywords = html_entity_decode(meta_keywords, ENT_QUOTES | ENT_HTML5, 'UTF-8');
