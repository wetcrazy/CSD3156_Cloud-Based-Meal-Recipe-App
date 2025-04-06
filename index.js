const express = require('express');
const AWS = require('aws-sdk');
const cors = require('cors');

// initialize expressn app
const app = express();
const port = process.env.PORT || 3000;
AWS.config.update({
  region:'us-east-1',
});

const s3 = new AWS.S3();

app.use(cors());
app.use(express.json());

app.post('/generate-presigned-url', (req, res) => {
  const { filename, filetype } = req.body;

  const s3Params = {
    Bucket: 'foodrecipe-bucket-1',
    Key: `public/${filename}`,
    Expires: 60,
    ContentType: filetype,
    ACL: 'public-read',
  };

  s3.getSignedUrl('putObject', s3Params, (err, url) => {
    if (err) {
      return res.status(500).json({ error: 'Error generating presigned URL' });
    }
    res.json({url});
  });
});

app.listen(port, () => {
  console.log(`Server is running on http://localhost:${port}`);
});
