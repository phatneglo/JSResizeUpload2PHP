<?php

  if( $_SERVER['REQUEST_METHOD']=='POST' && isset( $_POST['image'],$_POST['filename'] ) ){

        $image=$_POST['image'];
        // change your directory path here
        $savepath='./img-uploads/'; 
        // change the final format if you wish
        $filename=$_POST['filename'];
        $target=$savepath . $filename;

        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
        $result=file_put_contents( $target,  $data  );

        if ($result) {
            die (json_encode([
                'result' => 'Success',
                'filename' => $filename,
                'save_path' => $target
            ]));
        } else {
            die (json_encode(['result' => 'Failed', 'error' => var_dump ($result)]));
        }
    }
?>
<!DOCTYPE html>
<html>

<body>
    <form action="index.php" method="post" enctype="multipart/form-data">
        Select image to upload:
        <input type="file" name="fileToUpload" id="attachmentFile" accept="image/x-png,image/gif,image/jpeg">
        <input type="submit" value="Upload Image" name="submit">
        <input type="hidden" id="imgfilename" name="filename" value="">
    </form>
    <script>
    document.getElementById("attachmentFile").addEventListener("change", function(event) {
        JSImageCompress(event);
    });


    function JSImageCompress(e) {
        const width = 500;

        const fileField = e.target.files[0];
        const fileName = fileField.name;

        if (fileName == "") return false;

        const reader = new FileReader();
        reader.readAsDataURL(e.target.files[0]);
        reader.onload = event => {
            const img = new Image();
            img.src = event.target.result;

            img.onload = () => {
                const elem = document.createElement('canvas');
                const scaleFactor = width / img.width;
                elem.width = width;
                elem.height = img.height * scaleFactor;

                // Create a 2d canvass element to hold the image
                const ctx = elem.getContext('2d');

                ctx.drawImage(img, 0, 0, width, img.height * scaleFactor);
                ctx.canvas.toBlob((blob) => {
                    const file = new File([blob], fileName, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });

                }, 'image/jpeg', 1);


                let formData = new FormData();

                formData.append("filename", "resized" + fileName);
                formData.append("image", ctx.canvas.toDataURL('image/jpeg'));

                (async () => {
                    let response = await fetch('index.php', {
                        method: 'POST',
                        body: formData
                    });
                    let result = await response.json();
                    console.log(result);

                    if (result.result == 'Success') {
                        document.getElementById('attachmentFile').value = '';
                        document.getElementById('imgfilename').value = result.filename;
                        alert("Resized and Uploaded in " + result.save_path);
                    } else {
                        document.getElementById('attachmentFile').value = '';
                        alert(result.error);
                    }

                })();


            }
        }
    }
    </script>
</body>

</html>