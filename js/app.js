
var app = angular.module('app', ['ngMessages', 'ngRoute']);

// Route configuration
app.config(function($routeProvider){

    $routeProvider.when('/', {
        templateUrl: 'partials/login.html',
        controller : function($scope){
           // Defaults
           $scope.failure = false;
           $scope.success = false;

           // Form submit handler
           $scope.login = function(){
               if (this.password === 'test' && this.email === 'test@test.com'){
                   $scope.failure = false;
                   $scope.success = true;
               } else {
                   $scope.failure = true;
               }
           }
        }

    }).when('/grid', {
        templateUrl: 'partials/grid.html',
        controller: function($scope, $http){
            $http.get('backend/data.json').then(function(response){
                $scope.records = response.data;
            });
            
            $scope.pages = [1, 2, 3];
            $scope.activePage = 1;
            
            $scope.paginate = function(pageNumber){
                $scope.activePage = pageNumber;
            };
            
            $scope.isActivePage = function(pageNumber){
                return $scope.activePage == pageNumber;
            }
        }
    });
});
