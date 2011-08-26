-- http://t.co links shorten already shortened links; undo expansion to correct them

UPDATE tu_links SET expanded_url = '' WHERE expanded_URL != '' AND instr(url, 'http://t.co') > 0;