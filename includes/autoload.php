spl_autoload_register('myAutoLoader');

func myAutoLoader($fileName) {
    $fileExtension = ".php";
    $fullPath = $fileName . $fileExtension;

    include_once($fullPath);
}
