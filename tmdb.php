<?php
/*
=====================================================
 Author : Mehmet Hanoğlu <dle.net.tr>
-----------------------------------------------------
 License : MIT License
-----------------------------------------------------
 Copyright (c)
-----------------------------------------------------
 Date : 08.02.2018 [1.3]
=====================================================
*/

// E_DEPRECATED tanımını kontrol et
if (!defined('E_DEPRECATED')) {
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
    ini_set('error_reporting', E_ALL & ~E_WARNING & ~E_NOTICE);
} else {
    error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('error_reporting', E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_NOTICE);
}

class FilmReader
{

    public function get($url)
    {

        $html = preg_replace('/[^\d]/', '', $url);

        $id = $html;

        $apikey = "a0d71cffe2d6693d462af9e4f336bc06";

        $appendToResponse  = array('account_states', 'alternative_titles', 'credits', 'images','keywords', 'release_dates', 'trailers', 'videos', 'translations', 'similar', 'reviews', 'lists', 'changes', 'rating' , 'releases');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.themoviedb.org/3/movie/".$id."?api_key={$apikey}&language=tr-TR&append_to_response=". implode(',', (array) $appendToResponse));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
        $result = curl_exec($ch);
        curl_close($ch);
        $movie = json_decode($result,true);

        $ret['rating'] = isset($movie['vote_average']) ? round($movie['vote_average'], 1) : '';

        $imdb_url    = "https://www.imdb.com/title/" . $movie['imdb_id'];
        $tmdbid      = $movie['id'];
        $title       = $movie['original_title'];
        $description = $movie['overview'];
        $status      = $movie['status'];
        $homepage    = $movie['homepage'];
        $backdrop    = "http://image.tmdb.org/t/p/original" . $movie['backdrop_path'];
        $poster      = "http://image.tmdb.org/t/p/w500" . $movie['poster_path'];
        $ltitle      = $movie['title'];
        $year        = substr($movie['release_date'], 0, 4);
        $homepage    = $movie['homepage'];
        $releasen    = date("d.m.Y", strtotime($movie['release_date']));
        $runtime     = $movie['runtime'] . " min.";
        $vote        = implode(', ', $ret);
        $tagline     = $movie['tagline'];
        $status      = $movie['status'];
        $budget      = number_format($movie['budget']) . " \$";
        $revenue     = number_format($movie['revenue']) . " \$";

        if ($movie['poster_path']!=null){
                $images_small = 'https://image.tmdb.org/t/p/w185' . $movie['poster_path'];
        } elseif ($movie['backdrop_path']!=null){
                $images_small = 'https://image.tmdb.org/t/p/w185' . $movie['poster_path'];
        } else {
                $images_small = '/img/no-backdrop.png';
        }

        if ($movie['backdrop_path']!=null){
                $big_images = 'https://image.tmdb.org/t/p/original' . $movie['backdrop_path'];
        } elseif ($movie['backdrop_path']!=null){
                $big_images = 'https://image.tmdb.org/t/p/original' . $movie['backdrop_path'];
        } else {
                $big_images = '/img/no-backdrop.png';
        }
        

        
        if (is_array($movie['genres'])){
                foreach($movie['genres'] as $result) {$genre .= $result['name']. ', ';}
        }
        if (is_array($movie['spoken_languages'])){
                foreach($movie['spoken_languages'] as $result) {$languages .= $result['name'].' ';}
        }
        if (is_array($movie['production_companies'])){
                foreach($movie['production_companies'] as $result) {$companies .= $result['name'].', ';}
        }
        if (is_array($movie['production_countries'])){
                foreach($movie['production_countries'] as $result) {$country .= $result['name'].', ';}
        }
        if (is_array($movie['trailers']['youtube'])){
                foreach($movie['trailers']['youtube'] as $result) {$youtube = "https://www.youtube.com/embed/".$result['source'];}
        }
         
          $cast = $movie['credits']['cast'];
            $actors = array();
            $count = 0;
        foreach ($cast as $cast_member) {
                $actors[] = $cast_member['name'];
                $count++;
                if ($count == 8)
            break;
        }
        
          $actors = implode(", ", $actors);

           foreach ($movie['credits']['crew'] as $crew) {
            if ($crew['job'] == 'Screenplay') {
              $writer = $crew['name'];
            }
           }
        
        if(is_array($movie['credits']['crew'])) {
                    foreach($movie['credits']['crew'] as $crew) {
                        if ($crew['job'] == 'Director'){
                        $crewMember = $crew['name'];
                    }
                }
              
	      }
          $mpaa_rating = '';
            $age_rating = '';
            $releases = $movie['releases']['countries'];
            foreach ($releases as $release_item) {
                if ($release_item['iso_3166_1'] === 'US')
                    $mpaa_rating = $release_item['certification'];
                if ($release_item['iso_3166_1'] === 'DE')
                    $age_rating = $release_item['certification'];
            }
            

        $film = array(
                'img'           => $images_small,
            'namelong'          => $title,
            'name'                  => $ltitle,
                        'crating'               => $age_rating,
            'year'                  => $year,
            'url'                   => $url,
            'aspect'                => $imdb_url,
                'type'                  => $type,
            'soundtracks'           => $homepage,
            'sound'                     => $movie['status'],
                        'genres'                => $genre,
                        'runtime'               => $runtime,
                        'ratinga'                   => $vote,
                        'ratingb'                   => $mpaa_rating,
                        'ratingc'                   => $movie['vote_count'],
                        'actors'                => $actors,
                        'writers'               => $writer,
                        'screenman'             => $screenman,
                        'director'              => $crewMember,
                        'story'                     => $description,
                        'country'               => $country,
                        'language'              => $languages,
                        'datelocal'             => $releasen,
                        'color'                     => $revenue,
                        'budget'                => $budget,
                        'locations'             => $big_images,
                        'namelocal'             => $title,
                        'tagline'               => $tagline,
                        'productionfirm'        => $companies,
                        'backdrops'                 => $imge,
                     );
        return $film;
            } 
}


$f = new FilmReader();
echo "<pre>";
print_r($f->get("https://www.themoviedb.org/movie/336843-the-maze-runner-the-death-cure"));
print_r($f->get("https://www.themoviedb.org/movie/346672-underworld-blood-wars"));
print_r($f->get("https://www.themoviedb.org/movie/168259-furious-7"));
print_r($f->get("https://www.themoviedb.org/movie/107846-escape-plan"));
print_r($f->get("https://www.themoviedb.org/movie/238-the-godfather"));
print_r($f->get("https://www.themoviedb.org/movie/278-the-shawshank-redemption"));
echo "</pre>";
?>
