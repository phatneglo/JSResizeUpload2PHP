<?php
    if( $_SERVER['REQUEST_METHOD']=='POST' && isset( $_POST['image'],$_POST['filename'] ) ){

        $image=$_POST['image'];
        $filename=$_POST['filename'];

       
        $savepath='./uploads/'; #put your path here

        $target=$savepath . $filename;

        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
        $result=file_put_contents( $target,  $data  );


        header('HTTP/1.1 200 OK',true,200);
        header('Content-Type: text/plain');
        exit( $result ? $filename : sprintf( 'Unable to save %s',$filename ) );
    }
?>
<!DOCTYPE html>
<html>

<body>
    <form action="img-upload.php" method="post" enctype="multipart/form-data">
        Select image to upload:
        <input type="file" name="fileToUpload" id="attachmentFile" accept="image/x-png,image/gif,image/jpeg">
        <input type="submit" value="Upload Image" name="submit">
        <input type="hidden" id="imgfilename" name="filename" value="">
    </form>
    <script>
    document.getElementById("attachmentFile").addEventListener("change", function(event) {
        compress(event);
    });

    function compress(e) {
        // SET MO UNG FILE DITO
        const width = 800;
        const height = 640;
        const fileName = e.target.files[0].name;
        const reader = new FileReader();
        reader.readAsDataURL(e.target.files[0]);
        reader.onload = event => {
            const img = new Image();
            img.src = event.target.result;

            img.onload = () => {
                    const elem = document.createElement('canvas');
                    elem.width = width;
                    elem.height = height;
                    const ctx = elem.getContext('2d');
                    // img.width and img.height will contain the original dimensions
                    ctx.drawImage(img, 0, 0, width, height);
                    ctx.canvas.toBlob((blob) => {
                        const file = new File([blob], fileName, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                    }, 'image/jpeg', 1);

                    console.log(ctx.canvas.toDataURL('image/jpeg'));
                    var fd = new FormData();
                    fd.append('action', 'save');
                    fd.append('image', ctx.canvas.toDataURL('image/jpeg'));
                    fd.append('filename', fileName)


                    var ajax = function(url, data, callback) {
                        var xhr = new XMLHttpRequest();
                        xhr.onreadystatechange = function() {
                            if (this.readyState == 4 && this.status == 200) callback.call(this, this
                                .response);
                        };
                        xhr.open('POST', url, true);
                        xhr.send(data);
                    };

                    var callback = function(r) {
                        // ALERT MO DITO FOR TESTING
                        // alert(r)
                        document.getElementById('imgfilename').value = r;

                    }

                    ajax.call(this, location.href, fd, callback);



                },
                reader.onerror = error => console.log(error);
        };
    }
    </script>
</body>

</html>