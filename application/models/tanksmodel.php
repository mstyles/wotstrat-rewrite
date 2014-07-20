<?php
class TanksModel
{
    public function __autoload($class_name)
    {
        include dirname(dirname(__FILE__)) . "/classes/$class_name.php";
    }
    /**
     * Every model needs a database connection, passed to the model
     * @param object $db A PDO database connection
     */
    public function __construct($db)
    {
        try {
            $this->db = $db;
        } catch (PDOException $e) {
            exit('Database connection could not be established.');
        }
    }

    /**
     * Get all songs from database
     */
    public function loadTank($tank_id)
    {
        $sql = "
            SELECT
                t.*
            FROM tanks t
            WHERE t.id = $tank_id
        ";

        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch();

        $class_name = "Tank";
        include dirname(dirname(__FILE__)) . "/classes/$class_name.php";
        $tank = new Tank($result, $this->db);
        // echo $tank;
        $tmp_weight = $tank->getChassisWeight();
        $tank->loadModules();
        $tank->setChassisWeight($tmp_weight);
        return $tank;
    }

    /**
     * Add a song to database
     * @param string $artist Artist
     * @param string $track Track
     * @param string $link Link
     */
    public function addSong($artist, $track, $link)
    {
        // clean the input from javascript code for example
        $artist = strip_tags($artist);
        $track = strip_tags($track);
        $link = strip_tags($link);

        $sql = "INSERT INTO song (artist, track, link) VALUES (:artist, :track, :link)";
        $query = $this->db->prepare($sql);
        $query->execute(array(':artist' => $artist, ':track' => $track, ':link' => $link));
    }

    /**
     * Delete a song in the database
     * Please note: this is just an example! In a real application you would not simply let everybody
     * add/update/delete stuff!
     * @param int $song_id Id of song
     */
    public function deleteSong($song_id)
    {
        $sql = "DELETE FROM song WHERE id = :song_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':song_id' => $song_id));
    }
}
