app.controller("#ID#", function($scope, $http, $location, prompt, CONFIG){

	$scope.config = {
		title:"#TITLE#",
		createApi:"#CREATE_API#",
		redirectPath:"#REDIRECT_PATH#"
	};

	$scope.item = {};

	$scope.submit = function(){
		$http.post(CONFIG.API_BASE_URL + $scope.config.createApi, angular.copy($scope.item)).success(function(resp){
			if(resp.success){
				prompt({title:"#TITLE#", message:"등록성공"}).then(function(result){
					$location.path($scope.config.redirectPath);
				});
			}
			else
			{
				alert("등록 실패 : "+resp.error);
			}
		})
	}

});
