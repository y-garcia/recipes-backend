<?php
/**
 * Author: Yeray García Quintana
 * Date: 29.07.2018
 */

namespace Recipes\model;

class Recipe
{
    private $id;
    private $name;
    private $portions;
    private $duration;
    private $url;

    private $ingredients;
    private $steps;
    private $users;
    private $tags;

    /**
     * Recipe constructor.
     * @param int|string $id
     * @param string $name
     * @param int|string $portions
     * @param int|string $duration
     * @param string $url
     */
    public function __construct($id, $name, $portions, $duration, $url)
    {
        $this->id = $id;
        $this->name = $name;
        $this->portions = $portions;
        $this->duration = $duration;
        $this->url = $url;
        $this->ingredients = array();
        $this->steps = array();
        $this->users = array();
        $this->tags = array();
    }

    public static function getEmptyRecipe()
    {
        return new Recipe(null, null, null, null, null);
    }

    public static function fromPost($input)
    {
        $recipe = new Recipe(
            isset($input['id']) ? $input['id'] : null,
            isset($input['recipename']) ? $input['recipename'] : null,
            isset($input['servings']) ? $input['servings'] : null,
            isset($input['duration']) ? $input['duration'] : null,
            isset($input['url']) ? $input['url'] : null
        );

        $recipe->setIngredients(explode("\n", isset($input['ingredients']) ? $input['ingredients'] : null));
        $recipe->setSteps(explode("\n", isset($input['steps']) ? $input['steps'] : null));
        $recipe->setUsers(isset($input['users']) ? $input['users'] : array());
        $recipe->setTags(isset($input['tags']) ? $input['tags'] : array());

        return $recipe;
    }

    public static function fromArray($recipe, $ingredients, $steps, $users, $tags)
    {
        $result = new Recipe(
            isset($recipe['id']) ? $recipe['id'] : null,
            isset($recipe['name']) ? $recipe['name'] : null,
            isset($recipe['portions']) ? $recipe['portions'] : null,
            isset($recipe['duration']) ? $recipe['duration'] : null,
            isset($recipe['url']) ? $recipe['url'] : null
        );

        $result->setIngredients($ingredients);
        $result->setSteps($steps);
        $result->setUsers($users);
        $result->setTags($tags);

        return $result;
    }

    static public function fromJson($obj)
    {
        return new Recipe(
            isset($obj->id) ? $obj->id : null,
            isset($obj->name) ? $obj->name : null,
            isset($obj->portions) ? $obj->portions : null,
            isset($obj->duration) ? $obj->duration : null,
            isset($obj->url) ? $obj->url : null
        );
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int|string
     */
    public function getPortions()
    {
        return $this->portions;
    }

    /**
     * @param int|string $portions
     */
    public function setPortions($portions)
    {
        $this->portions = $portions;
    }

    /**
     * @return int|string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return int|string
     */
    public function getDurationInMinutes()
    {
        return $this->duration / 60;
    }

    /**
     * @param int|string $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getIngredients()
    {
        return $this->ingredients;
    }

    public function getIngredientsAsString()
    {
        return is_array($this->ingredients) ? implode("\n", $this->ingredients) : $this->ingredients;
    }

    /**
     * @param array $ingredients
     */
    public function setIngredients($ingredients)
    {
        $this->ingredients = $ingredients;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    public function getStepsAsString()
    {
        return is_array($this->steps) ? implode("\n", $this->steps) : $this->steps;
    }

    /**
     * @param array $steps
     */
    public function setSteps($steps)
    {
        $this->steps = $steps;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param array $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

}
