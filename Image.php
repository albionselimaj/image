<?php namespace App\Library\Image;

use Storage;
use Intervention\Image\Facades\Image as Intervention;

class Image {

	/**
     * Quality of images in percentage
     */
	const QUALITY = 80;

	/**
     * Possible file sizes
     */
	protected $sizes = [
	'big'    => [800, 600],
	'icon'   => [48, 48],
	'small'  => [96, 96],
	'medium' => [400, 400],
	];

	/**
     * The image object from the request
     */
	protected $image = null;

	/**
     * Assigns file value from $request to the
     * protected $image attribute
     *
     * @param $image $request->image
     */
	public function __construct( $image ) {
		$this->image = $image;
	}

	/**
     * @return $image instance filename
     */
	public function name() {
		return $this->image;
	}

	/**
     * Get instantiated image on the requested size
     *
     * @param $size
     */
	public function get( $size = "original") {
		$name = $this->getName( $this->image, $size );

		if( !self::exists($name) ) {
			$size = $this->sizes[$size];

			$img = Intervention::make( self::storage($this->image) )->fit($size[0], $size[1]);

			self::save($img, $name);
		}

		return url("../storage/app/images/{$name}");
	}

	/**
     * Get the requested file name with the specified size
     *
     * @param $name filename
     * @param $size
     */
	protected function getName( $name, $size = null ) {
		if(isset($this->sizes[$size])) {
			$size     = $this->sizes[$size];
			$basename = pathinfo($name, PATHINFO_FILENAME);
			$ext      = pathinfo($name, PATHINFO_EXTENSION);

			return "{$basename}-{$size[0]}x{$size[1]}.{$ext}";
		}

		return $name;
	}

	/**
     * Returns the storage path for images
     * and append the file name if requested
     *
     * @param $filename
     */
	public static function storage( $filename = null ) {
		return storage_path("app\images\\$filename");
	}

	/**
     * Check whether the given image exists
     * in the images storage
     *
     * @param $filename
     */
	public static function exists( $filename ) {
		return Storage::exists( "images/$filename" );
	}

	/**
     * Create new image directly from the request
     *
     * @param $image $request->image
     * @param $name Filename
     */
	public static function create( $image, $name ) {
		$name = $name . '-' . time() . '.' . $image->getClientOriginalExtension();
		$img = Intervention::make( $image );

		self::save($img, $name);

		return new Image($name);
	}

	/**
     * Saves the newly created image in the storage
     *
     * @param $image Intervention Instance
     * @param $name Filename
     */
	public static function save( $image, $name ) {
		$image->save( self::storage($name), self::QUALITY);
	}

	/**
     * Deletes the specified resource
     *
     * @param $filename Filename
     */
	public static function delete($filename) {
		$image = new Image($filename);

		foreach ($image->sizes as $size => $dimensions) {
			$resized = $image->getName($image->name(), $size);

			if(self::exists($resized)) {
				Storage::delete("images/{$resized}");
			}
		}

		return Storage::delete("images/{$filename}");
	}
}