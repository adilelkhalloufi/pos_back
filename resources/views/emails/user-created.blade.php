<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
        }
        .info-group {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 New Client Created</h1>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            <p>A new client has been registered in your system. Here are the details:</p>
            
            <div class="info-group">
                <span class="label">Name:</span>
                <p>{{ $user->name }}</p>
            </div>
            
            <div class="info-group">
                <span class="label">Email:</span>
                <p>{{ $user->email }}</p>
            </div>
            
            <div class="info-group">
                <span class="label">Phone:</span>
                <p>{{ $user->phone ?? 'N/A' }}</p>
            </div>
            
            <div class="info-group">
                <span class="label">Role:</span>
                <p>{{ $user->role }}</p>
            </div>
            
            <div class="info-group">
                <span class="label">Account Status:</span>
                <p>{{ $user->statue }}</p>
            </div>
            
            <div class="info-group">
                <span class="label">Registration Date:</span>
                <p>{{ $user->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
            
            <p>Thank you!</p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
