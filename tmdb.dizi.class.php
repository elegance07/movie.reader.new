
<?php
/*
=====================================================
 Author : Mehmet Hano&#287;lu <dle.net.tr>
-----------------------------------------------------
 License : MIT License
-----------------------------------------------------
 Copyright (c)
-----------------------------------------------------
 Date : 28.09.2018 [2.5]
=====================================================
*/

error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
  
class FilmReader {
    private array $config = [
        'screens' => true,  // Ekran görüntülerini çekme ayarı
        'screens_count' => 5, // Çekilecek ekran görüntüsü sayısı
    ];

    public function get(string $url): array {
        $apikey = "a0d71cffe2d6693d462af9e4f336bc06";
        $kurl = parse_url($url);
        $id = preg_split('#([0-9]+)#', $url, null, PREG_SPLIT_DELIM_CAPTURE)[1];

        $cti = curl_init();
        curl_setopt_array($cti, [
            CURLOPT_URL => "http://api.themoviedb.org/3/tv/".$id."?language=en-null&append_to_response=videos&api_key=" . $apikey,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER => FALSE,
            CURLOPT_HTTPHEADER => ["Accept: application/json"]
        ]);
        $response14 = curl_exec($cti);
        curl_close($cti);
        $series = json_decode($response14, true);

        $cm = curl_init();
        curl_setopt_array($cm, [
            CURLOPT_URL => "http://api.themoviedb.org/3/tv/".$id."?language=tr-TR&append_to_response=videos,credits,content_ratings,images,external_ids&include_image_language=en,null&api_key=" . $apikey,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER => FALSE,
            CURLOPT_HTTPHEADER => ["Accept: application/json"]
        ]);
        $response7 = curl_exec($cm);
        curl_close($cm);
        $moviedata = json_decode($response7, true);

        $runtimem = $moviedata['episode_run_time'][0] . " dk.";
        $ret['rating'] = match(true) {
            isset($moviedata['vote_average']) => round($moviedata['vote_average'], 1),
            default => null,
        };

        $revenue = '$' . number_format($moviedata['revenue']);
        $budget = '$' . number_format($moviedata['budget']);

        $film = [];
        $film['name'] = $moviedata['name'];
        $film['namelocal'] = $moviedata['original_name'];
        $film['img'] = 'https://image.tmdb.org/t/p/w500' . $moviedata['poster_path'];
        $film['locations'] = 'https://image.tmdb.org/t/p/original' . $moviedata['backdrop_path'];
        $film['url'] = $url;
        $film['year'] = substr($moviedata['first_air_date'], 0, 4);
        $film['datelocal'] = date("d.m.Y", strtotime($moviedata['first_air_date']));
        $film['runtime'] = $runtimem;
        $film['tagline'] = $moviedata['tagline'];
        $film['budget'] = $budget;
        $film['tmdb_id'] = $moviedata['id'];
        $film['sound'] = $moviedata['status'];
        $film['aspect'] = $moviedata['external_ids']['imdb_id'];
        $film['ratinga'] = implode(', ', $ret);
        $film['ratingc'] = (int) $moviedata['vote_count'];
        $film['episodes'] = number_format($moviedata['number_of_episodes']);
        $film['seasons'] = number_format($moviedata['number_of_seasons']);
        $film['country'] = implode(', ', array_column($moviedata['production_countries'], 'name'));
        $film['productionfirm'] = implode(', ', array_column($moviedata['production_companies'], 'name'));
        $film['genres'] = implode(', ', array_column($moviedata['genres'], 'name'));
        $film['language'] = implode(', ', array_column($moviedata['spoken_languages'], 'name'));
        $film['director'] = implode(', ', array_column($moviedata['created_by'], 'name'));
        $film['productionfirm'] = implode(', ', array_column($moviedata['networks'], 'name'));

        $imgs = [];
        if (is_array($moviedata['images']['backdrops'])) {
            foreach ($moviedata['images']['backdrops'] as $result) {
                $imgs[] = '[img]https://image.tmdb.org/t/p/original'.$result['file_path'].'[/img]';
            }
        }
        $imge = array_slice($imgs, 0, $this->config['screens_count']);
        $film['backdrops'] = implode($imge);

        $youtube = [];
        if (is_array($series['videos']['results'])) {
            foreach ($series['videos']['results'] as $result) {
                $youtube[] = '<option value="https://www.youtube.com/embed/'.$result['key'].'">'.$result['name'].'</option>';
            }
        }
        $film['type'] = implode($youtube);

        $cast = $moviedata['credits']['cast'];
        $actors = [];
        $count = 0;
        foreach ($cast as $cast_member) {
            $actors[] = $cast_member['name'];
            $count++;
            if ($count == 8)
                break;
        }
        $film['actors'] = implode(", ", $actors);

        $film['story'] = $moviedata['overview'];

        $mpaa_rating = '';
        $age_rating = '';
        $releases = $moviedata['content_ratings']['results'];
        foreach ($releases as $release_item) {
            if ($release_item['iso_3166_1'] === 'US')
                $mpaa_rating = $release_item['rating'];
            if ($release_item['iso_3166_1'] === 'DE')
                $age_rating = $release_item['rating'];
        }
        $film['age'] = $age_rating . '+';
        $film['ratingb'] = $mpaa_rating;
        
        return $film;
    }
}

$f = new FilmReader();
echo "<pre>";
print_r($f->get("https://www.themoviedb.org/tv/1396-breaking-bad"));
print_r($f->get("https://www.themoviedb.org/tv/1405-dexter"));
print_r($f->get("https://www.themoviedb.org/tv/4607-lost"));
print_r($f->get("https://www.themoviedb.org/tv/1639-heroes"));
print_r($f->get("https://www.themoviedb.org/tv/1705-fringe"));
print_r($f->get("https://www.themoviedb.org/tv/10545-true-blood"));
echo "</pre>";

?>
