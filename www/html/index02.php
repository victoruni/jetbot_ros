<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />

<script type="text/javascript" src="js/roslib.min.js"></script>
<script type="text/javascript" src="js/nipplejs.js"></script>
<script type="text/javascript" type="text/javascript">
  var ros = new ROSLIB.Ros({
    url : 'ws://<?php echo $_SERVER['SERVER_NAME']; ?>:9090'
  });

  ros.on('connection', function() {
    document.getElementById("status").innerHTML = "Connected";
  });

  ros.on('error', function(error) {
    document.getElementById("status").innerHTML = "Error";
  });

  ros.on('close', function() {
    document.getElementById("status").innerHTML = "Closed";
  });

  var txt_listener = new ROSLIB.Topic({
    ros : ros,
    name : '/txt_msg',
    messageType : 'std_msgs/String'
  });

  txt_listener.subscribe(function(m) {
    document.getElementById("msg").innerHTML = m.data;
  });

  cmd_vel_listener = new ROSLIB.Topic({
    ros : ros,
    name : "/jetbot_motors/cmd_raw",
    messageType : 'geometry_msgs/Twist'
  });
 

  pub_motors_str=new ROSLIB.Topic({
     ros: ros,
     name : "/jetbot_motors/cmd_str",
     messageType : 'std_msgs/String'  
  }); 

  function moveFLRBS(str) {  
     var msg=new ROSLIB.Message ({'data' : str});
     pub_motors_str.publish(msg);    
  } 

  move = function (linear, angular) {
    var twist = new ROSLIB.Message({
      linear: {
        x: linear,
        y: 0,
        z: 0
      },
      angular: {
        x: 0,
        y: 0,
        z: angular
      }
    });
    cmd_vel_listener.publish(twist);
  }


    createJoystick = function () {
      var options = {
        zone: document.getElementById('zone_joystick'),
        threshold: 0.1,
        position: { left: 50 + '%' },
        mode: 'static',
        size: 150,
        color: '#000000',
      };
      manager = nipplejs.create(options);
      linear_speed = 0;
      angular_speed = 0;
      manager.on('start', function (event, nipple) {
        timer = setInterval(function () {
          move(linear_speed, angular_speed);
        }, 250);
      });
      manager.on('move', function (event, nipple) {
        max_linear = 1.0; // m/s
        max_angular = 1.0; // rad/s
        max_distance = 75.0; // pixels;
        linear_speed = Math.sin(nipple.angle.radian) * max_linear * nipple.distance/max_distance;
				angular_speed = -Math.cos(nipple.angle.radian) * max_angular * nipple.distance/max_distance;
      });
      manager.on('end', function () {
        if (timer) {
          clearInterval(timer);
        }
        self.move(0, 0);
      });
    }
    window.onload = function () {
      createJoystick();
    }

 

</script>

</head>

<body>
  <h1>Simple ROS User Interface</h1>
  <p>Connection status: <span id="status"></span></p>
  <p>Last /txt_msg received: <span id="msg"></span></p>
  <table>
     <tr>
        <td> </td><td><button onclick="moveFLRBS('forward')">F</button></td><td> </td>
     <tr>
     <tr>
        <td><button onclick="moveFLRBS('left')">L</button></td>
        <td><button onclick="moveFLRBS('stop')">S</button></td>
        <td><button onclick="moveFLRBS('right')">R</button></td>
     <tr>
     <tr>
        <td> </td><td><button onclick="moveFLRBS('backward')">B</button></td><td> </td>
     <tr>
  </table>
  <div id="zone_joystick" style="position: relative;"></div>
<br><br>
<img id=camera src="http://<?php echo $_SERVER['SERVER_NAME']; ?>:8090/stream?topic=/jetbot_camera/raw&width=320&height=240">
</body>
</html>
