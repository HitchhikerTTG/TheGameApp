
<!-- let's do it powoli-->

<script>
    $(document).ready(function () {

      $('#TrwajaceMecze').load( "/live");

      function refresh() {
//            $('#result').html(new Date($.now("H:i:s")));
            $('#TrwajaceMecze').load( "/live");
        }

        setInterval(function () {
            refresh()
        }, 60000); //60 seconds
    });



</script>
</body>
</html>