<?php
// Parameters
$type            = $_GET['type'];
$ckEditorFuncNum = $_GET['CKEditor'];
$funcNum         = $_GET['CKEditorFuncNum'];

if (isset($_SERVER['HTTPS']))
{
	$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
}
else
{
	$protocol = 'http';
}

$base_dir = __DIR__;
$base     = str_replace('media/sellacious/js/plugins', '', dirname($base_dir));
$parsed   = parse_url($protocol . '://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
$root_url = $parsed['scheme'] . '://' . $parsed['host'] . (str_replace('media/sellacious/js/plugins/ckeditor4/upload.php', '', $parsed['path']));

$allowedExtensions = array(
	"png",
	"jpg",
	"jpeg",
);

if ($type == 'file')
{
	$allowedExtensions = array(
		"doc",
		"pdf",
		"docx",
	);
}

// Get image file extension
$fileExtension = pathinfo($_FILES["upload"]["name"], PATHINFO_EXTENSION);

if (in_array(strtolower($fileExtension), $allowedExtensions))
{
	if (!file_exists($base . '/images/ckeditor4/uploads'))
	{
		mkdir($base . '/images/ckeditor4/uploads', 0755, true);
	}

	$filename = $_FILES['upload']['name'];
	$name     = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
	$name     = $name . '_' . getRandomString() . '.' . $fileExtension;

	if (move_uploaded_file($_FILES['upload']['tmp_name'], $base . '/images/ckeditor4/uploads/' . $name))
	{
		// File path
		$url = $root_url . "images/ckeditor4/uploads/" . $name;

		echo '<script>window.parent.CKEDITOR.tools.callFunction(' . $funcNum . ', "' . $url . '", "")</script>';
	}

}

function getRandomString($length = 6)
{
	$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
	return substr(str_shuffle($permitted_chars), 0, $length);
}

exit;
