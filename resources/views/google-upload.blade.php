<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
          margin: 0;
          padding: 0;
          height: 100vh;
          display: flex;
          justify-content: center;
          align-items: center;
          background-color: #f7f7f7;
        }
        
        .auth-container {
          width: 100%;
          max-width: 400px;
          padding: 20px;
          border: 1px solid #ccc;
          border-radius: 8px;
          background-color: white;
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
          margin-top: 50px;
        }
        
        h2 {
          text-align: center;
          margin-bottom: 20px;
        }
        
        .input-group {
          margin-bottom: 15px;
        }
        
        label {
          display: block;
          margin-bottom: 5px;
        }
        
        input {
          width: 100%;
          padding: 8px;
          border: 1px solid #ccc;
          border-radius: 4px;
        }
        .input-image{
            
            /* margin-right: 5px */
        }
        
        button {
          width: 100%;
          padding: 10px;
          background-color: #b7b7b7;
          color: #fff;
          border: none;
          border-radius: 4px;
          cursor: pointer;
        }
        
        button:hover {
          background-color: #b6a675;
        }
        </style>
</head>
<body>
    <div class="auth-container">
        <h2>Upload Employee Profile Image</h2>
        <form action="\upload-profile-image-on-google" method="post" enctype="multipart/form-data">
            @csrf
          <div class="input-group">
            <label for="file">Image</label>
            <input id="file" name="file" type="file"  class="input-image"/>

            @error('file')
              <span style="color: red">{{$message}}</span>
            @enderror

          </div>
          <button type="submit">Upload</button>
        </form>
      </div>
</body>
</html>



 
  