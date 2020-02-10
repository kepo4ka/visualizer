<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Sunburst - Sunburst of a Directory Tree</title>

    <!-- CSS Files -->
    <link type="text/css" href="css/base.css" rel="stylesheet"/>
    <link type="text/css" href="css/Sunburst.css" rel="stylesheet"/>


    <!-- JIT Library File -->
    <script language="javascript" type="text/javascript" src="js/jit-yc.js"></script>

    <!-- Example File -->
    <script language="javascript" type="text/javascript" src="js/example2.js"></script>
</head>

<body onload="init();">
<div id="container">

    <div id="left-container">


        <div class="text">
            <h4>
                Sunburst of a Directory Tree
            </h4>

            A static JSON Tree structure is used as input for this visualization.<br/><br/>
            Tips are used to describe the file size and its last modified date.<br/><br/>
            <b>Left click</b> to rotate the Sunburst to the selected node and see its details.

        </div>
        <div id="id-list"></div>

    </div>

    <div id="center-container">
        <div id="infovis"></div>
    </div>

    <div id="right-container">

        <div id="inner-details"></div>

    </div>

    <div id="log"></div>
</div>
</body>
</html>
