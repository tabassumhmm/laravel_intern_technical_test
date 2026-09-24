{ pkgs ? import <nixpkgs> { } }:

pkgs.mkShell {
  packages = [
    (pkgs.php84.withExtensions ({ all, ... }: with all; [ pdo_sqlite filter mbstring dom tokenizer xmlwriter session ]))
    pkgs.php84.packages.composer
    pkgs.sqlite
    pkgs.curl
    pkgs.git
  ];
}
