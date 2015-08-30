/*
 * +---------------------+
 * | Packages table      |
 * +----+------+---------+
 * | id | name | version |
 * +----+------+---------+
 *
 * CREATE TABLE Packages(ID int PIMARY KEY NOT NULL, Name varchar(100) NOT NULL, Version int NOT NULL);
*/

var mysql = require('mysql');

var mySqlClient = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : '',
  database : "viper-packages-bdd"
});

function exists(filename, version = "") {
  // requête bdd
  var query = (version)
    ? "SELECT * FROM Packages WHERE Name = '" + filename + "' AND Version = " + version + ";"
    : "SELECT * FROM Packages WHERE Name = '" + filename + "' ORDER BY Version DESC LIMIT 1;";
  mySqlClient.query(query, function select(error, results, fields) {
    if (error) {
      mySqlClient.end();
      return;
    }
    if (results.length > 0)  { 
      /*var firstResult = results[ 0 ];
      console.log('id: ' + firstResult['id']);
      console.log('label: ' + firstResult['label']);
      console.log('valeur: ' + firstResult['valeur']);*/
    } else {
      //console.log("Pas de données");
    }
    mySqlClient.end();
  });
}
