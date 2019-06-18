<?php

namespace eCreeth\Relationships;

trait RelationComment {

	public static function oneToOne()
	{
		return "| A one-to-one relationship is a very basic relation. For example, a User model might be associated with one Phone.";
	}

	public static function oneToMany()
	{
		return "| A one-to-many relationship is used to define relationships where a single model owns any amount of other models.\n| For example, a blog post may have an infinite number of comments.";
	}

	public static function manyToMany()
	{
		return "| A many-to-many relationship is used to define relationships in which a model has any number of other models.\n| For example, many users may have the role of \"Admin\" and the admin role may belongs to many users.\n| To define this relationship, three database tables are needed: users, roles, and role_user. \n| The role_user table is derived from the alphabetical order of the related model names, and contains the user_id and role_id columns.";
	}

	public static function hasOneThrough()
	{
		return "| The has-one-through relationship links models through a single intermediate relation.\n| For example, if each \"supplier\" (AS ACCESSOR) has one \"user\" (AS INTERMATE MODEL), and each user is associated\n| with one user \"history\" (AS A MODEL THAT WE WANT TO ACCESS) record,\n| then the supplier model may access the user's history through the user.";
	}

	public static function hasManyThrough()
	{
		return "| The \"has-many-through\" relationship provides a convenient shortcut for accessing distant relations via an intermediate relation.\n| For example, a \"Country\" model might have many \"Post\" models through an intermediate \"User\" model.\n| In this example, you could easily gather all blog posts for a given country.";
	}

	public static function oneToOnePolymorphic()
	{
		return "| A \"one-to-one polymorphic\" relation is similar to a simple \"one-to-one\" relation;\n| however, the target model can belong to more than one type of model on a single association.\n| For example, a blog \"Post\" and a \"User\" may share a polymorphic relation to an \"Image\" model. Using a one-to-one polymorphic relation\n| allows you to have a single list of unique images that are used for both blog posts and user accounts.";
	}

	public static function oneToManyPolymorphic()
	{
		return "| A \"one-to-many polymorphic\" relation is similar to a simple one-to-many relation;\n| however, the target model can belong to more than one type of model on a single association. For example,\n| imagine users of your application can \"comment\" on both \"posts\" and \"videos\". Using polymorphic relationships,\n| you may use a single comments table for both of these scenarios.";
	}

	public static function manyToManyPolymorphic()
	{
		return "A blog Post and Video model could share a polymorphic relation to a Tag model. Using a many-to-many polymorphic relation \nallows you to have a single list of unique tags that are shared across blog posts and videos.";
	}

}