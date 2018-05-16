# AMOLA

CLI Tool to making CRUD API code automatically with specification file.

Just write down 'URL like API spec' and run, then you can get API codes.

## INSTALL

```
npm install -g amola
```

---


## USAGE

### Start project

Initialize default setting at current path.

`.amola` file will be generated. that contains configurations.



```bash
$ amola init
```


### Make CRUD API codes

Generate codes with specification file `spec.amola`. the codes will be generated in `gen` folder.



```bash
$ amola run
```

you can get more information with `-h` option.

---

## EXAMPLE

`spec.amola` is looks like codes below.

```
# Read rows from `users` table by `userId` or `email` fields
	table:users
	get_userInformations.php?userId=<int@>&email=<string@>

# Update 'price' field' of 'sales' table by customer's name
	table:sales
	update_sales.php?customer_name=<string*@>&price=<int>
```


## EXTRA INFO


### build system setting - Sublime text

```
{ "cmd": ["amola.cmd", "run"] }
```

---

## TODO

* Add test unit
* API Document HTML build
* `.amola` should be change as Array.
