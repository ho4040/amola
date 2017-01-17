#AMOLA

CLI Tool to making CRUD API code automatically with specification file.
Just write down 'URL like API spec' and run, then you can get API codes.

##INSTALL

```
npm install -g amola
```

---


##USAGE

### Start project

Initialize default setting at current path.
`.amola` file will be generated. that contains configurations.
you can get more information with '-h' options.

```
amola init
```


### Make CRUD APIs

Generate codes with specification file `spec.amola`
the codes will be generated in `gen` folder.
you can get more information with '-h' options.

```
amola run
```

---

##EXTRA INFO


### build system setting - Sublime text

```
{ "cmd": ["amola.cmd", "run"] }
```

---

##TODO

* Add test unit
* API Document HTML build
* .amola spec change as Array. for now it's Object.