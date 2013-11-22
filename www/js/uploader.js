			function uploadFiles(url, files){

				var formData = new FormData();
				for (var i = 0, file; file = files[i]; ++i) {
					formData.append(file.name, file);
					// formData.append("files", files[0]);

					var xhr = new XMLHttpRequest();
					xhr.open('POST', url, true);

					var progressBar = document.getElementById('progress');
					xhr.upload.onprogress = function(e) {
						if (e.lengthComputable) {
							progressBar.value = (e.loaded / e.total) * 100;
							progressBar.textContent = progressBar.value; // Fallback for unsupported browsers.
						}
					};
					xhr.onload = function() {
						if (this.status == 200) {

							var resp = JSON.parse(this.response);

							if (resp.error) {
								console.log(resp.error);
							} else {
								var avatarWp = document.getElementById('file-list');
									avatarWp.innerHTML = resp.src;

								// var avatarImg = avatarWp.getElementsByTagName('img');
								// if (avatarImg.length !== 0){
								// 	avatarImg[0].src = resp.dataUrl;
								// }
								// else {
								// 	var image = document.createElement('img');
								// 	image.src = resp.dataUrl;
								// 	avatarWp.appendChild(image);
								// }
							}

						}
					};
					xhr.send(formData);
				}
			}

			document.querySelector('input[type="file"]').addEventListener('change', function(e){
				uploadFiles({plink //Upload:default $form['token']->value}, this.files);
			}, false);