public static function nickToSlug(string $nick): string
{
    $map = ['ą'=>'a','ć'=>'c','ę'=>'e','ł'=>'l','ń'=>'n','ó'=>'o','ś'=>'s','ź'=>'z','ż'=>'z',
            'Ą'=>'a','Ć'=>'c','Ę'=>'e','Ł'=>'l','Ń'=>'n','Ó'=>'o','Ś'=>'s','Ź'=>'z','Ż'=>'z'];
    $slug = strtr($nick, $map);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

public static function uniqueSlug(string $base, string $excludeUniID = ''): string
{
    $db = \Config\Database::connect();
    $candidate = $base;
    $i = 1;
    while (true) {
        $q = $db->table('uzytkownicy')->where('slug', $candidate);
        if ($excludeUniID) $q->where('uniID !=', $excludeUniID);
        if ($q->countAllResults() === 0) return $candidate;
        $candidate = $base . '-' . $i++;
    }
}