<!-- Template based off of project code provided by the kind folks at http://tympanus.net/codrops/ -->
<!DOCTYPE html>
<html lang="en" class="no-js">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Now App</title>
    <meta name="description" content="Real time user tracking for visualizing and storing performance metrics on production web pages" />
    <meta name="keywords" content="RUM JS, Real User Monitoring, Performance, Tracking, Timing, Google, Analytics, OO Charts, Episodes, Codrop, Matthew Sniff" />
    <meta name="author" content="Matthew Sniff" />
    <link rel="icon" type="image/png" href="assets/favicon.png">
  </head>


<style type="text/css">

  body {
    padding: 0;
    margin: 0;
    font-family: 'Europa', 'Helvetica Neue', sans-serif;
    background-color: rgb(50,50,50);
    background-image: url('assets/lake.jpg');
    background-repeat: no-repeat;
    background-size: cover;
  }

  .wrapper {
    width:700px;
    overflow: hidden;
    margin: auto;
    text-align: left;
  }

  .container {
    width: 300px;
    margin-top: 10%;
    overflow: hidden;
    float: left;
  }

  .container > img {
    float: left;
    height: 600px;
  }

  .containerRight {
    width: 400px;
    margin-top: 10%;
    overflow: hidden;
    float: left;
  }

  .containerRight > img {
    width: 400px;
  }

  .containerRight > div {
    width: 380px;
    padding: 10px 30px;
    font-size: 20px;
    line-height: 30px;
    font-weight: 300;
    color: white;
    font-family: "Helvetica Neue", Helvetica;
  }

  #appstorebutton {
    width: 200px;
    margin-top: 20px;
    margin-left: 20px;
  }

</style>

  <body>

  <!--Wrapper-->
  <div class="wrapper">

    <!--iPhone-->
    <div class="container">
        <img src="assets/screenex.png" />
    </div>

    <!--Right Container-->
    <div class="containerRight">
        <img src="assets/logowhite.png" />
        <div>
          Get advanced weather conditions for your area instantly - all on a beautiful display.
          <br /><br />
          Real. Fast. Weather.
        </div>
        <img id="appstorebutton" src="assets/appstore.png" />
    </div>

  </div>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
  </body>
</html>
